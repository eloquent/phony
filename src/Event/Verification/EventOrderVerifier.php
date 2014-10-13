<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event\Verification;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Eloquent\Phony\Event\NullEvent;

/**
 * Checks and asserts the order of events.
 *
 * @internal
 */
class EventOrderVerifier implements EventOrderVerifierInterface
{
    /**
     * Get the static instance of this verifier.
     *
     * @return EventOrderVerifierInterface The static verifier.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new event order verifier.
     *
     * @param AssertionRecorderInterface|null $assertionRecorder The assertion recorder to use.
     * @param AssertionRendererInterface|null $assertionRenderer The assertion renderer to use.
     */
    public function __construct(
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null
    ) {
        if (null === $assertionRecorder) {
            $assertionRecorder = AssertionRecorder::instance();
        }
        if (null === $assertionRenderer) {
            $assertionRenderer = AssertionRenderer::instance();
        }

        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
    }

    /**
     * Get the assertion recorder.
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    public function assertionRecorder()
    {
        return $this->assertionRecorder;
    }

    /**
     * Get the assertion renderer.
     *
     * @return AssertionRendererInterface The assertion renderer.
     */
    public function assertionRenderer()
    {
        return $this->assertionRenderer;
    }

    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @param EventCollectionInterface $events,... The events.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkInOrder()
    {
        return $this->checkInOrderSequence(func_get_args());
    }

    /**
     * Throws an exception unless the supplied events happened in chronological
     * order.
     *
     * @param EventCollectionInterface $events,... The events.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function inOrder()
    {
        return $this->inOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence happened in chronological order.
     *
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkInOrderSequence($events)
    {
        if (!count($events)) {
            return $this->assertionRecorder->createSuccess();
        }

        $isMatch = true;
        $matchingEvents = array();
        $earliestEvent = null;

        foreach ($events as $eventCollection) {
            if (!$eventCollection->hasEvents()) {
                $isMatch = false;

                break;
            }

            if ($eventCollection instanceof EventInterface) {
                if (null === $earliestEvent) {
                    $matchingEvents[] = $earliestEvent = $eventCollection;

                    continue;
                }

                if (
                    $eventCollection->sequenceNumber() >
                    $earliestEvent->sequenceNumber()
                ) {
                    $matchingEvents[] = $earliestEvent = $eventCollection;

                    continue;
                }
            } else {
                if (null === $earliestEvent) {
                    $matchingEvents[] = $earliestEvent =
                        $eventCollection->firstEvent();

                    continue;
                }

                foreach ($eventCollection->events() as $event) {
                    if (
                        $event->sequenceNumber() >
                        $earliestEvent->sequenceNumber()
                    ) {
                        $matchingEvents[] = $earliestEvent = $event;

                        continue 2;
                    }
                }
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
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function inOrderSequence($events)
    {
        if ($result = $this->checkInOrderSequence($events)) {
            return $result;
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

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Expected events in order:\n%s\n%s",
                $this->assertionRenderer
                    ->renderEvents($this->expectedEvents($events)),
                $renderedActual
            )
        );
    }

    /**
     * Attempts to normalize the supplied event order expectation into a
     * meaningful sequence of singular events.
     *
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @return EventCollectionInterface The normalized events.
     */
    protected function expectedEvents($events)
    {
        $expected = array();
        $earliestEvent = null;

        foreach ($events as $eventCollection) {
            if ($eventCollection instanceof EventInterface) {
                $expected[] = $earliestEvent = $eventCollection;
            } else {
                $event = null;

                if (null === $earliestEvent) {
                    $event = $eventCollection->firstEvent();
                } else {
                    foreach ($eventCollection->events() as $event) {
                        if (
                            $event->sequenceNumber() >
                            $earliestEvent->sequenceNumber()
                        ) {
                            break;
                        }
                    }
                }

                if (null === $event) {
                    $event = NullEvent::instance();
                }

                $expected[] = $earliestEvent = $event;
            }
        }

        return new EventCollection($expected);
    }

    /**
     * Merge the supplied event sequence into a single event collection, in
     * chronological order.
     *
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @param EventCollectionInterface $events The ordered events.
     */
    protected function mergeEvents($events)
    {
        $merged = array();

        foreach ($events as $eventCollection) {
            if ($eventCollection instanceof EventInterface) {
                $merged[$eventCollection->sequenceNumber()] = $eventCollection;
            } else {
                foreach ($eventCollection->events() as $thisEvent) {
                    $merged[$thisEvent->sequenceNumber()] = $thisEvent;
                }
            }
        }

        ksort($merged);

        return new EventCollection($merged);
    }

    private static $instance;
    private $assertionRecorder;
    private $assertionRenderer;
}
