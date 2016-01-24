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
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Iterator;

/**
 * An abstract base class for implementing events.
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * Construct a new event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function __construct($sequenceNumber, $time)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
    }

    /**
     * Get the sequence number.
     *
     * The sequence number is a unique number assigned to every event that Phony
     * records. The numbers are assigned sequentially, meaning that sequence
     * numbers can be used to determine event order.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time()
    {
        return $this->time;
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return true;
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return boolean True if this collection contains any calls.
     */
    public function hasCalls()
    {
        return false;
    }

    /**
     * Get the number of events.
     *
     * @return integer The event count.
     */
    public function eventCount()
    {
        return 1;
    }

    /**
     * Get the number of calls.
     *
     * @return integer The call count.
     */
    public function callCount()
    {
        return 0;
    }

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        return $this->eventCount();
    }

    /**
     * Get all events as an array.
     *
     * @return array<EventInterface> The events.
     */
    public function allEvents()
    {
        return array($this);
    }

    /**
     * Get all calls as an array.
     *
     * @return array<CallInterface> The calls.
     */
    public function allCalls()
    {
        return array();
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
        if (0 === $index || -1 === $index) {
            return $this;
        }

        throw new UndefinedEventException($index);
    }

    /**
     * Get the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
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
        throw new UndefinedCallException($index);
    }

    /**
     * Get the arguments.
     *
     * @return ArgumentsInterface|null The arguments.
     * @throws UndefinedCallException  If there are no calls.
     */
    public function arguments()
    {
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
        throw new UndefinedCallException(0);
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator(array($this));
    }

    private $sequenceNumber;
    private $time;
}
