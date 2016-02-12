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

use ArrayIterator;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Collection\Exception\UndefinedIndexException;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Collection\IndexNormalizerInterface;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Iterator;

/**
 * Represents a collection of events.
 */
class EventCollection implements EventCollectionInterface
{
    /**
     * Construct a new event collection.
     *
     * @param array<EventInterface>         $events          The events.
     * @param IndexNormalizerInterface|null $indexNormalizer The index normalizer to use.
     */
    public function __construct(
        array $events = array(),
        IndexNormalizerInterface $indexNormalizer = null
    ) {
        if (null === $indexNormalizer) {
            $indexNormalizer = IndexNormalizer::instance();
        }

        $calls = array();

        foreach ($events as $event) {
            if ($event instanceof CallInterface) {
                $calls[] = $event;
            }
        }

        $this->events = $events;
        $this->indexNormalizer = $indexNormalizer;
        $this->calls = $calls;
        $this->eventCount = count($events);
        $this->callCount = count($calls);
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return $this->eventCount > 0;
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return boolean True if this collection contains any calls.
     */
    public function hasCalls()
    {
        return $this->callCount > 0;
    }

    /**
     * Get the number of events.
     *
     * @return integer The event count.
     */
    public function eventCount()
    {
        return $this->eventCount;
    }

    /**
     * Get the number of calls.
     *
     * @return integer The call count.
     */
    public function callCount()
    {
        return $this->callCount;
    }

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        return $this->eventCount;
    }

    /**
     * Get all events as an array.
     *
     * @return array<EventInterface> The events.
     */
    public function allEvents()
    {
        return $this->events;
    }

    /**
     * Get all calls as an array.
     *
     * @return array<CallInterface> The calls.
     */
    public function allCalls()
    {
        return $this->calls;
    }

    /**
     * Get the first event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent()
    {
        if (!$this->events) {
            throw new UndefinedEventException(0);
        }

        return $this->events[0];
    }

    /**
     * Get the last event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent()
    {
        if ($count = count($this->events)) {
            return $this->events[$count - 1];
        }

        throw new UndefinedEventException(0);
    }

    /**
     * Get an event by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt($index = 0)
    {
        try {
            $normalized = $this->indexNormalizer
                ->normalize($this->eventCount, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedEventException($index, $e);
        }

        return $this->events[$normalized];
    }

    /**
     * Get the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
        if (isset($this->calls[0])) {
            return $this->calls[0];
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get the last call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall()
    {
        if ($count = count($this->calls)) {
            return $this->calls[$count - 1];
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get a call by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt($index = 0)
    {
        try {
            $normalized = $this->indexNormalizer
                ->normalize($this->callCount, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedCallException($index, $e);
        }

        return $this->calls[$normalized];
    }

    /**
     * Get the arguments.
     *
     * @return ArgumentsInterface|null The arguments.
     * @throws UndefinedCallException  If there are no calls.
     */
    public function arguments()
    {
        foreach ($this->calls as $call) {
            return $call->arguments();
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get an argument by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedCallException     If there are no calls.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function argument($index = 0)
    {
        foreach ($this->calls as $call) {
            return $call->arguments()->get($index);
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->events);
    }

    private $events;
    private $indexNormalizer;
    private $calls;
    private $eventCount;
    private $callCount;
}
