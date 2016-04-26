<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use InvalidArgumentException;

/**
 * Checks and asserts the order of events.
 */
class EventOrderVerifier
{
    /**
     * Get the static instance of this verifier.
     *
     * @return EventOrderVerifier The static verifier.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance(),
                NullEvent::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new event order verifier.
     *
     * @param AssertionRecorder $assertionRecorder The assertion recorder to use.
     * @param AssertionRenderer $assertionRenderer The assertion renderer to use.
     */
    public function __construct(
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        NullEvent $nullEvent
    ) {
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->nullEvent = $nullEvent;
    }

    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public function checkInOrder()
    {
        return $this->checkInOrderSequence(func_get_args());
    }

    /**
     * Throws an exception unless the supplied events happened in chronological
     * order.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function inOrder()
    {
        return $this->inOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence happened in chronological order.
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public function checkInOrderSequence($events)
    {
        if (!count($events)) {
            return null;
        }

        $isMatch = true;
        $matchingEvents = array();
        $earliestEvent = null;

        foreach ($events as $event) {
            if ($event instanceof Event) {
                if (
                    !$earliestEvent ||
                    $event->sequenceNumber() > $earliestEvent->sequenceNumber()
                ) {
                    $matchingEvents[] = $earliestEvent = $event;

                    continue;
                }
            } elseif ($event instanceof EventCollection) {
                if (!$event->hasEvents()) {
                    $isMatch = false;

                    break;
                }

                foreach ($event->allEvents() as $subEvent) {
                    if (
                        !$earliestEvent ||
                        (
                            $subEvent->sequenceNumber() >
                            $earliestEvent->sequenceNumber()
                        )
                    ) {
                        $matchingEvents[] = $earliestEvent = $subEvent;

                        continue 2;
                    }
                }
            } else {
                if (is_object($event)) {
                    $type = var_export(get_class($event), true);
                } else {
                    $type = gettype($event);
                }

                throw new InvalidArgumentException(
                    sprintf(
                        'Cannot verify event order with supplied value of ' .
                            'type %s.',
                        $type
                    )
                );
            }

            $isMatch = false;

            break;
        }

        if ($isMatch) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless the supplied event sequence happened in
     * chronological order.
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function inOrderSequence($events)
    {
        if ($result = $this->checkInOrderSequence($events)) {
            return $result;
        }

        if (!count($events)) {
            return $this->assertionRecorder
                ->createFailure('Expected events. No events recorded.');
        }

        $mergedEvents = $this->mergeEvents($events);

        if ($mergedEvents->hasEvents()) {
            $renderedActual = sprintf(
                "Order:\n%s",
                $this->assertionRenderer->renderEvents($mergedEvents)
            );
        } else {
            $renderedActual = 'No events recorded.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                "Expected events in order:\n%s\n%s",
                $this->assertionRenderer
                    ->renderEvents($this->expectedEvents($events)),
                $renderedActual
            )
        );
    }

    /**
     * Checks that at least one event is supplied.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public function checkAnyOrder()
    {
        return $this->checkAnyOrderSequence(func_get_args());
    }

    /**
     * Throws an exception unless at least one event is supplied.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function anyOrder()
    {
        return $this->anyOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence contains at least one event.
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public function checkAnyOrderSequence($events)
    {
        if (!count($events)) {
            return null;
        }

        return $this->assertionRecorder
            ->createSuccess($this->mergeEvents($events)->allEvents());
    }

    /**
     * Throws an exception unless the supplied event sequence contains at least
     * one event.
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function anyOrderSequence($events)
    {
        if ($result = $this->checkAnyOrderSequence($events)) {
            return $result;
        }

        return $this->assertionRecorder
            ->createFailure('Expected events. No events recorded.');
    }

    /**
     * Attempts to normalize the supplied event order expectation into a
     * meaningful sequence of singular events.
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection The normalized events.
     */
    protected function expectedEvents($events)
    {
        $expected = array();
        $earliestEvent = null;

        foreach ($events as $event) {
            if ($event instanceof Event) {
                $expected[] = $earliestEvent = $event;
            } else {
                $subEvent = null;

                foreach ($event->allEvents() as $subEvent) {
                    if (
                        !$earliestEvent ||
                        (
                            $subEvent->sequenceNumber() >
                            $earliestEvent->sequenceNumber()
                        )
                    ) {
                        break;
                    }
                }

                if (!$subEvent) {
                    $subEvent = $this->nullEvent;
                }

                $expected[] = $earliestEvent = $subEvent;
            }
        }

        return new EventSequence($expected);
    }

    /**
     * Merge the supplied event sequence into a single event collection, in
     * chronological order.
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @param EventCollection $events The ordered events.
     */
    protected function mergeEvents($events)
    {
        $merged = array();

        foreach ($events as $event) {
            if ($event instanceof Event) {
                $merged[$event->sequenceNumber()] = $event;
            } else {
                foreach ($event->allEvents() as $subEvent) {
                    $merged[$subEvent->sequenceNumber()] = $subEvent;
                }
            }
        }

        ksort($merged);

        return new EventSequence(array_values($merged));
    }

    private static $instance;
    private $assertionRecorder;
    private $assertionRenderer;
    private $nullEvent;
}
