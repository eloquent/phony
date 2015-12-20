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
 *
 * @api
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
     * @api
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
     * @api
     *
     * @return integer The call count.
     */
    public function callCount();

    /**
     * Get all events as an array.
     *
     * @return array<EventInterface> The events.
     */
    public function allEvents();

    /**
     * Get all calls as an array.
     *
     * @api
     *
     * @return array<CallInterface> The calls.
     */
    public function allCalls();

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
    public function eventAt($index = 0);

    /**
     * Get the first call.
     *
     * @api
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall();

    /**
     * Get the last call.
     *
     * @api
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall();

    /**
     * Get a call by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer $index The index.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt($index = 0);

    /**
     * Get the arguments.
     *
     * @api
     *
     * @return ArgumentsInterface|null The arguments.
     * @throws UndefinedCallException  If there are no calls.
     */
    public function arguments();

    /**
     * Get an argument by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedCallException     If there are no calls.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function argument($index = 0);
}
