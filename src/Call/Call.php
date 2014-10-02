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
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Exception;
use InvalidArgumentException;
use ReflectionFunctionAbstract;

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
        $this->endEvent = null;
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

        if ($event instanceof EndEventInterface) {
            $this->endEvent = $event;
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
     * Get the end event.
     *
     * @return EndEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent()
    {
        return $this->endEvent;
    }

    /**
     * Get the non-'called', non-end events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function otherEvents()
    {
        return $this->otherEvents;
    }

    /**
     * Get the called function or method called.
     *
     * @return ReflectionFunctionAbstract The function or method called.
     */
    public function reflector()
    {
        return $this->calledEvent->reflector();
    }

    /**
     * Get the $this value.
     *
     * @return object|null The $this value, or null if unbound.
     */
    public function thisValue()
    {
        return $this->calledEvent->thisValue();
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
        if ($this->endEvent instanceof ReturnedEventInterface) {
            return $this->endEvent->returnValue();
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

    private $events;
    private $calledEvent;
    private $endEvent;
    private $otherEvents;
}
