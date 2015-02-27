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
 * Represents a collection of events.
 *
 * @internal
 */
class EventCollection implements EventCollectionInterface
{
    /**
     * Construct a new event collection.
     *
     * @param array<integer,EventInterface>|null $events The events.
     */
    public function __construct(array $events = null)
    {
        if (null === $events) {
            $events = array();
        }

        $this->events = $events;
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return $this->events && true;
    }

    /**
     * Get the events.
     *
     * @return array<integer,EventInterface> The events.
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * Get the first event.
     *
     * @return EventInterface|null The first event, or null if there are no events.
     */
    public function firstEvent()
    {
        if ($this->events) {
            return $this->events[0];
        }
    }

    /**
     * Get the last event.
     *
     * @return EventInterface|null The last event, or null if there are no events.
     */
    public function lastEvent()
    {
        if ($this->events) {
            return $this->events[count($this->events) - 1];
        }
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

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        return count($this->events);
    }

    protected $events;
}
