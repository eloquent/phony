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
     * @param array<intger,CallEventInterface> The call events.
     */
    public function __construct(array $events)
    {
        $this->setEvents($events);
    }

    /**
     * Set the events.
     *
     * @param array<integer,CallEventInterface> $events The events.
     */
    public function setEvents(array $events)
    {
        if (!isset($events[0]) || !$events[0] instanceof CalledEventInterface) {
            throw new InvalidArgumentException(
                'Calls must have at least one event, ' .
                    'and the first event must be an instance of ' .
                    'Eloquent\Phony\Call\Event\CalledEventInterface.'
            );
        }

        $this->events = array();
        $this->calledEvent = $events[0];
        $this->responseEvent = null;
        $this->otherEvents = array();

        $this->addEvents($events);
    }

    /**
     * Add a sequence of events.
     *
     * @param array<integer,CallEventInterface> $events The events.
     */
    public function addEvents(array $events)
    {
        foreach ($events as $event) {
            $this->addEvent($event);
        }
    }

    /**
     * Add an event.
     *
     * @param CallEventInterface $event The event.
     */
    public function addEvent(CallEventInterface $event)
    {
        $this->events[] = $event;

        if ($event instanceof ResponseEventInterface) {
            $this->responseEvent = $event;
        } elseif (!$event instanceof CalledEventInterface) {
            $this->otherEvents[] = $event;
        }
    }

    /**
     * Get the events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function events()
    {
        return $this->events;
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
     * Get the response event.
     *
     * @return ResponseEventInterface|null The response event, or null if the call has not yet completed.
     */
    public function responseEvent()
    {
        return $this->responseEvent;
    }

    /**
     * Get the non-'called', non-response events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function otherEvents()
    {
        return $this->otherEvents;
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

    private $events;
    private $calledEvent;
    private $responseEvent;
    private $otherEvents;
}
