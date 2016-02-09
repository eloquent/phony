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

use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\EndEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\TraversableEventInterface;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Error;
use Exception;
use InvalidArgumentException;

/**
 * The interface implemented by calls.
 *
 * @api
 */
interface CallInterface extends EventInterface, EventCollectionInterface
{
    /**
     * Returns true if this call has responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @api
     *
     * @return boolean True if this call has responded.
     */
    public function hasResponded();

    /**
     * Returns true if this call has responded with a traversable.
     *
     * @api
     *
     * @return boolean True if this call has responded with a traversable.
     */
    public function isTraversable();

    /**
     * Returns true if this call has responded with a generator.
     *
     * @api
     *
     * @return boolean True if this call has responded with a generator.
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
     * @api
     *
     * @return boolean True if this call has completed.
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
     * @api
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value.
     */
    public function returnValue();

    /**
     * Get the thrown exception.
     *
     * @api
     *
     * @return Exception|Error            The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception.
     */
    public function exception();

    /**
     * Get the response.
     *
     * @api
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
     * @api
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
     * @api
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime();

    /**
     * Get the 'called' event.
     *
     * @return CalledEventInterface The 'called' event.
     */
    public function calledEvent();

    /**
     * Set the response event.
     *
     * @param ResponseEventInterface $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already responded.
     */
    public function setResponseEvent(ResponseEventInterface $responseEvent);

    /**
     * Get the response event.
     *
     * @return ResponseEventInterface|null The response event, or null if the call has not yet responded.
     */
    public function responseEvent();

    /**
     * Add a traversable event.
     *
     * @param TraversableEventInterface $traversableEvent The traversable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addTraversableEvent(
        TraversableEventInterface $traversableEvent
    );

    /**
     * Get the traversable events.
     *
     * @return array<TraversableEventInterface> The traversable events.
     */
    public function traversableEvents();

    /**
     * Set the end event.
     *
     * @param EndEventInterface $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(EndEventInterface $endEvent);

    /**
     * Get the end event.
     *
     * @return EndEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent();
}
