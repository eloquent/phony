<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use Countable;
use IteratorAggregate;

/**
 * The interface implemented by event collections.
 */
interface EventCollectionInterface extends IteratorAggregate, Countable
{
    /**
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents();

    /**
     * Get the events.
     *
     * @return array<integer,EventInterface> The events.
     */
    public function events();

    /**
     * Get the first event.
     *
     * @return EventInterface|null The first event, or null if there are no events.
     */
    public function firstEvent();

    /**
     * Get the last event.
     *
     * @return EventInterface|null The last event, or null if there are no events.
     */
    public function lastEvent();
}
