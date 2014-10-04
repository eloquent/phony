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
use Eloquent\Phony\Call\Event\GeneratorEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Exception;
use InvalidArgumentException;

/**
 * Represents a single call.
 *
 * @internal
 */
class Call implements CallInterface
{
    /**
     * Construct a new call.
     *
     * @param CalledEventInterface                        $calledEvent     The 'called' event.
     * @param ResponseEventInterface|null                 $responseEvent   The response event, or null if the call has not yet completed.
     * @param array<integer,GeneratorEventInterface>|null $generatorEvents The generator events.
     */
    public function __construct(
        CalledEventInterface $calledEvent,
        ResponseEventInterface $responseEvent = null,
        array $generatorEvents = null
    ) {
        if (null === $generatorEvents) {
            $generatorEvents = array();
        }

        $this->calledEvent = $calledEvent;
        $this->responseEvent = $responseEvent;
        $this->generatorEvents = $generatorEvents;
    }

    /**
     * Get the 'called' event.
     *
     * @return CalledEventInterface The 'called' event.
     */
    public function calledEvent()
    {
        return $this->calledEvent;
    }

    /**
     * Set the 'response' event.
     *
     * @param ResponseEventInterface $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setResponseEvent(ResponseEventInterface $responseEvent)
    {
        if ($this->responseEvent) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $this->responseEvent = $responseEvent;
    }

    /**
     * Get the response event.
     *
     * @return ResponseEventInterface|null The response event, or null if the call has not yet completed.
     */
    public function responseEvent()
    {
        return $this->responseEvent;
    }

    /**
     * Add a generator event.
     *
     * @param GeneratorEventInterface $event The generator event.
     */
    public function addGeneratorEvent(GeneratorEventInterface $event)
    {
        $this->generatorEvents[] = $event;
    }

    /**
     * Get the generator events.
     *
     * @return array<integer,GeneratorEventInterface> The generator events.
     */
    public function generatorEvents()
    {
        return $this->generatorEvents;
    }

    /**
     * Get the events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function events()
    {
        $events = $this->generatorEvents();

        if ($this->responseEvent) {
            array_unshift($events, $this->responseEvent);
        }

        array_unshift($events, $this->calledEvent);

        return $events;
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback()
    {
        return $this->calledEvent->callback();
    }

    /**
     * Get the received arguments.
     *
     * @return array<integer,mixed> The received arguments.
     */
    public function arguments()
    {
        return $this->calledEvent->arguments();
    }

    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->calledEvent->sequenceNumber();
    }

    /**
     * Get the time at which the call was made.
     *
     * @return float The time at which the call was made, in seconds since the Unix epoch.
     */
    public function startTime()
    {
        return $this->calledEvent->time();
    }

    /**
     * Get the returned value.
     *
     * @return mixed The returned value.
     */
    public function returnValue()
    {
        if ($this->responseEvent instanceof ReturnedEventInterface) {
            return $this->responseEvent->returnValue();
        }
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|null The thrown exception, or null if no exception was thrown.
     */
    public function exception()
    {
        if ($this->responseEvent instanceof ThrewEventInterface) {
            return $this->responseEvent->exception();
        }
    }

    /**
     * Get the time at which the call completed.
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime()
    {
        if ($this->responseEvent) {
            return $this->responseEvent->time();
        }
    }

    private $calledEvent;
    private $responseEvent;
    private $generatorEvents;
}
