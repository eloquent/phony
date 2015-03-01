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

use Countable;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
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
     * Returns true if this collection contains any calls.
     *
     * @return boolean True if this collection contains any calls.
     */
    public function hasCalls();

    /**
     * Get the number of events.
     *
     * @return integer The event count.
     */
    public function eventCount();

    /**
     * Get the number of calls.
     *
     * @return integer The call count.
     */
    public function callCount();

    /**
     * Get all events as an array.
     *
     * @return array<integer,EventInterface> The events.
     */
    public function allEvents();

    /**
     * Get all calls as an array.
     *
     * @return array<integer,CallInterface> The calls.
     */
    public function allCalls();

    /**
     * Get an event by index.
     *
     * @param integer|null $index The index, or null for the first event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt($index = null);

    /**
     * Get a call by index.
     *
     * @param integer|null $index The index, or null for the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt($index = null);

    /**
     * Get the arguments.
     *
     * @return ArgumentsInterface|null The arguments.
     * @throws UndefinedCallException  If there are no calls.
     */
    public function arguments();

    /**
     * Get an argument by index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return mixed                      The argument.
     * @throws UndefinedCallException     If there are no calls.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function argument($index = null);
}
