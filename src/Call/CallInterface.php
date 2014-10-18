<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\TraversableEventInterface;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Event\EventInterface;
use Exception;
use InvalidArgumentException;

/**
 * The interface implemented by calls.
 */
interface CallInterface extends EventInterface
{
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
     * @return array<integer,TraversableEventInterface> The traversable events.
     */
    public function traversableEvents();

    /**
     * Set the end event.
     *
     * @param ResponseEventInterface $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(ResponseEventInterface $endEvent);

    /**
     * Get the end event.
     *
     * @return ResponseEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent();

    /**
     * Returns true if this call has responded.
     *
     * @return boolean True if this call has responded.
     */
    public function hasResponded();

    /**
     * Returns true if this call has responded with a traversable.
     *
     * @return boolean True if this call has responded with a traversable.
     */
    public function isTraversable();

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return boolean True if this call has responded with a generator.
     */
    public function isGenerator();

    /**
     * Returns true if this call has completed.
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
     * Get the received arguments.
     *
     * @return array<integer,mixed> The received arguments.
     */
    public function arguments();

    /**
     * Get an argument by index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function argument($index = null);

    /**
     * Get the returned value.
     *
     * @return mixed The returned value.
     */
    public function returnValue();

    /**
     * Get the thrown exception.
     *
     * @return Exception|null The thrown exception, or null if no exception was thrown.
     */
    public function exception();

    /**
     * Get the time at which the call responded.
     *
     * @return float|null The time at which the call responded, in seconds since the Unix epoch, or null if the call has not yet responded.
     */
    public function responseTime();

    /**
     * Get the time at which the call completed.
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime();
}
