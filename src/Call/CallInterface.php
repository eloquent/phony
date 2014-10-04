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

use Eloquent\Phony\Call\Event\CallEventInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Exception;

/**
 * The interface implemented by calls.
 */
interface CallInterface
{
    /**
     * Set the events.
     *
     * @param array<integer,CallEventInterface> $events The events.
     */
    public function setEvents(array $events);

    /**
     * Add a sequence of events.
     *
     * @param array<integer,CallEventInterface> $events The events.
     */
    public function addEvents(array $events);

    /**
     * Add an event.
     *
     * @param CallEventInterface $event The event.
     */
    public function addEvent(CallEventInterface $event);

    /**
     * Get the events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function events();

    /**
     * Get the 'called' event.
     *
     * @return CalledEventInterface The 'called' event.
     */
    public function calledEvent();

    /**
     * Get the response event.
     *
     * @return ResponseEventInterface|null The response event, or null if the call has not yet completed.
     */
    public function responseEvent();

    /**
     * Get the non-'called', non-response events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function otherEvents();

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
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber();

    /**
     * Get the time at which the call was made.
     *
     * @return float The time at which the call was made, in seconds since the Unix epoch.
     */
    public function startTime();

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
     * Get the time at which the call completed.
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime();
}
