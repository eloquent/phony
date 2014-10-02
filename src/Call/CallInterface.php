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
use Eloquent\Phony\Call\Event\EndEventInterface;
use Exception;
use ReflectionFunctionAbstract;

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
     * Get the end event.
     *
     * @return EndEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent();

    /**
     * Get the non-'called', non-end events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function otherEvents();

    /**
     * Get the called function or method called.
     *
     * @return ReflectionFunctionAbstract The function or method called.
     */
    public function reflector();

    /**
     * Get the $this value.
     *
     * @return object|null The $this value, or null if unbound.
     */
    public function thisValue();

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
