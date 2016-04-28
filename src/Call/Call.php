<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\EndEvent;
use Eloquent\Phony\Call\Event\ResponseEvent;
use Eloquent\Phony\Call\Event\TraversableEvent;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Error;
use Exception;
use InvalidArgumentException;

/**
 * The interface implemented by calls.
 */
interface Call extends Event, EventCollection
{
    /**
     * Returns true if this call has responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return bool True if this call has responded.
     */
    public function hasResponded();

    /**
     * Returns true if this call has responded with a traversable.
     *
     * @return bool True if this call has responded with a traversable.
     */
    public function isTraversable();

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return bool True if this call has responded with a generator.
     */
    public function isGenerator();

    /**
     * Returns true if this call has completed.
     *
     * When generator spies are in use, a call that returns a generator will not
     * be considered complete until the generator has been completely consumed
     * via iteration.
     *
     * Similarly, when traversable spies are in use, a call that returns a
     * traversable will not be considered complete until the traversable has
     * been completely consumed via iteration.
     *
     * @return bool True if this call has completed.
     */
    public function hasCompleted();

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback();

    /**
     * Get the returned value.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value.
     */
    public function returnValue();

    /**
     * Get the thrown exception.
     *
     * @return Exception|Error            The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception.
     */
    public function exception();

    /**
     * Get the response.
     *
     * @return tuple<Exception|Error|null,mixed> A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException        If this call has not yet responded.
     */
    public function response();

    /**
     * Get the time at which the call responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return float|null The time at which the call responded, in seconds since the Unix epoch, or null if the call has not yet responded.
     */
    public function responseTime();

    /**
     * Get the time at which the call completed.
     *
     * When generator spies are in use, a call that returns a generator will not
     * be considered complete until the generator has been completely consumed
     * via iteration.
     *
     * Similarly, when traversable spies are in use, a call that returns a
     * traversable will not be considered complete until the traversable has
     * been completely consumed via iteration.
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime();

    /**
     * Get the 'called' event.
     *
     * @return CalledEvent The 'called' event.
     */
    public function calledEvent();

    /**
     * Set the response event.
     *
     * @param ResponseEvent $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already responded.
     */
    public function setResponseEvent(ResponseEvent $responseEvent);

    /**
     * Get the response event.
     *
     * @return ResponseEvent|null The response event, or null if the call has not yet responded.
     */
    public function responseEvent();

    /**
     * Add a traversable event.
     *
     * @param TraversableEvent $traversableEvent The traversable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addTraversableEvent(
        TraversableEvent $traversableEvent
    );

    /**
     * Get the traversable events.
     *
     * @return array<TraversableEvent> The traversable events.
     */
    public function traversableEvents();

    /**
     * Set the end event.
     *
     * @param EndEvent $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(EndEvent $endEvent);

    /**
     * Get the end event.
     *
     * @return EndEvent|null The end event, or null if the call has not yet completed.
     */
    public function endEvent();
}
