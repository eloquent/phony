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
use Generator;
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
     * @param ResponseEventInterface|null                 $responseEvent   The response event, or null if the call has not yet responded.
     * @param array<integer,GeneratorEventInterface>|null $generatorEvents The generator events.
     * @param ResponseEventInterface|null                 $endEvent        The end event, or null if the call has not yet completed.
     *
     * @throws InvalidArgumentException If the supplied calls respresent an invalid call state.
     */
    public function __construct(
        CalledEventInterface $calledEvent,
        ResponseEventInterface $responseEvent = null,
        array $generatorEvents = null,
        ResponseEventInterface $endEvent = null
    ) {
        $calledEvent->setCall($this);
        $this->calledEvent = $calledEvent;

        $this->generatorEvents = array();

        if ($responseEvent) {
            $this->setResponseEvent($responseEvent);
        }

        if (null !== $generatorEvents) {
            foreach ($generatorEvents as $generatorEvent) {
                $this->addGeneratorEvent($generatorEvent);
            }
        }

        if ($endEvent) {
            $this->setEndEvent($endEvent);
        }
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
     * Set the response event.
     *
     * @param ResponseEventInterface $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already responded.
     */
    public function setResponseEvent(ResponseEventInterface $responseEvent)
    {
        if ($this->responseEvent) {
            throw new InvalidArgumentException('Call already responded.');
        }

        $responseEvent->setCall($this);
        $this->responseEvent = $responseEvent;

        if (!$this->isGenerator()) {
            $this->endEvent = $responseEvent;
        }
    }

    /**
     * Get the response event.
     *
     * @return ResponseEventInterface|null The response event, or null if the call has not yet responded.
     */
    public function responseEvent()
    {
        return $this->responseEvent;
    }

    /**
     * Add a generator event.
     *
     * @param GeneratorEventInterface $generatorEvent The generator event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addGeneratorEvent(GeneratorEventInterface $generatorEvent)
    {
        if ($this->endEvent) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $generatorEvent->setCall($this);
        $this->generatorEvents[] = $generatorEvent;
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
     * Set the end event.
     *
     * @param ResponseEventInterface $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(ResponseEventInterface $endEvent)
    {
        if ($this->endEvent) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $endEvent->setCall($this);

        if (!$this->responseEvent) {
            $this->responseEvent = $endEvent;
        }

        $this->endEvent = $endEvent;
    }

    /**
     * Get the end event.
     *
     * @return ResponseEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent()
    {
        return $this->endEvent;
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
            if ($this->endEvent && $this->isGenerator()) {
                $events[] = $this->endEvent;
            }

            array_unshift($events, $this->responseEvent);
        }

        array_unshift($events, $this->calledEvent);

        return $events;
    }

    /**
     * Returns true if this call has responded.
     *
     * @return boolean True if this call has responded.
     */
    public function hasResponded()
    {
        return $this->responseEvent && true;
    }

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return boolean True if this call has responded with a generator.
     */
    public function isGenerator()
    {
        return $this->responseEvent instanceof ReturnedEventInterface &&
            $this->responseEvent->value() instanceof Generator;
    }

    /**
     * Returns true if this call has completed.
     *
     * @return boolean True if this call has completed.
     */
    public function hasCompleted()
    {
        return $this->endEvent && true;
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
            return $this->responseEvent->value();
        }
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|null The thrown exception, or null if no exception was thrown.
     */
    public function exception()
    {
        if ($this->endEvent instanceof ThrewEventInterface) {
            return $this->endEvent->exception();
        }
    }

    /**
     * Get the time at which the call responded.
     *
     * @return float|null The time at which the call responded, in seconds since the Unix epoch, or null if the call has not yet responded.
     */
    public function responseTime()
    {
        if ($this->responseEvent) {
            return $this->responseEvent->time();
        }
    }

    /**
     * Get the time at which the call completed.
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime()
    {
        if ($this->endEvent) {
            return $this->endEvent->time();
        }
    }

    private $calledEvent;
    private $responseEvent;
    private $generatorEvents;
    private $endEvent;
}
