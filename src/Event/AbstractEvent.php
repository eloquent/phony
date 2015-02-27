<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use ArrayIterator;
use Iterator;

/**
 * An abstract base class for implementing events.
 *
 * @internal
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
     * Get the events.
     *
     * @return array<integer,EventInterface> The events.
     */
    public function events()
    {
        return array($this);
    }

    /**
     * Get the first event.
     *
     * @return EventInterface|null The first event, or null if there are no events.
     */
    public function firstEvent()
    {
        return $this;
    }

    /**
     * Get the last event.
     *
     * @return EventInterface|null The last event, or null if there are no events.
     */
    public function lastEvent()
    {
        return $this;
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

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        return 1;
    }

    private $sequenceNumber;
    private $time;
}
