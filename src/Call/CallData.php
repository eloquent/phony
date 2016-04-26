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

use ArrayIterator;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\EndEvent;
use Eloquent\Phony\Call\Event\ResponseEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\TraversableEvent;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Error;
use Exception;
use Generator;
use InvalidArgumentException;
use Iterator;
use Traversable;

/**
 * Represents a single call.
 */
class CallData implements Call
{
    /**
     * Construct a new call data instance.
     *
     * @param CalledEvent $calledEvent The 'called' event.
     *
     * @throws InvalidArgumentException If the supplied calls respresent an invalid call state.
     */
    public function __construct(CalledEvent $calledEvent)
    {
        $calledEvent->setCall($this);
        $this->calledEvent = $calledEvent;

        $this->traversableEvents = array();
    }

    /**
     * Get the sequence number.
     *
     * The sequence number is a unique number assigned to every event that Phony
     * records. The numbers are assigned sequentially, meaning that sequence
     * numbers can be used to determine event order.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->calledEvent->sequenceNumber();
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time()
    {
        return $this->calledEvent->time();
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return true;
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return boolean True if this collection contains any calls.
     */
    public function hasCalls()
    {
        return true;
    }

    /**
     * Get the number of events.
     *
     * @return integer The event count.
     */
    public function eventCount()
    {
        $events = $this->allEvents();

        return count($events);
    }

    /**
     * Get the number of calls.
     *
     * @return integer The call count.
     */
    public function callCount()
    {
        return 1;
    }

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        $events = $this->allEvents();

        return count($events);
    }

    /**
     * Get the first event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent()
    {
        return $this->calledEvent;
    }

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent()
    {
        if ($this->endEvent) {
            return $this->endEvent;
        }

        if ($events = $this->traversableEvents()) {
            return $events[count($events) - 1];
        }

        if ($this->responseEvent) {
            return $this->responseEvent;
        }

        return $this->calledEvent;
    }

    /**
     * Get an event by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt($index = 0)
    {
        if (0 === $index) {
            return $this->calledEvent;
        }

        $events = $this->allEvents();
        $count = count($events);

        if (!$this->normalizeIndex($count, $index, $normalized)) {
            throw new UndefinedEventException($index);
        }

        return $events[$normalized];
    }

    /**
     * Get the first call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
        return $this;
    }

    /**
     * Get the last call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall()
    {
        return $this;
    }

    /**
     * Get a call by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt($index = 0)
    {
        if (0 === $index || -1 === $index) {
            return $this;
        }

        throw new UndefinedCallException($index);
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator(array($this));
    }

    /**
     * Get the 'called' event.
     *
     * @return CalledEvent The 'called' event.
     */
    public function calledEvent()
    {
        return $this->calledEvent;
    }

    /**
     * Set the response event.
     *
     * @param ResponseEvent $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already responded.
     */
    public function setResponseEvent(ResponseEvent $responseEvent)
    {
        if ($this->responseEvent) {
            throw new InvalidArgumentException('Call already responded.');
        }

        $responseEvent->setCall($this);
        $this->responseEvent = $responseEvent;
    }

    /**
     * Get the response event.
     *
     * @return ResponseEvent|null The response event, or null if the call has not yet responded.
     */
    public function responseEvent()
    {
        return $this->responseEvent;
    }

    /**
     * Add a traversable event.
     *
     * @param TraversableEvent $traversableEvent The traversable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addTraversableEvent(
        TraversableEvent $traversableEvent
    ) {
        if (!$this->isTraversable()) {
            throw new InvalidArgumentException('Not a traversable call.');
        }
        if ($this->endEvent) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $traversableEvent->setCall($this);
        $this->traversableEvents[] = $traversableEvent;
    }

    /**
     * Get the traversable events.
     *
     * @return array<TraversableEvent> The traversable events.
     */
    public function traversableEvents()
    {
        return $this->traversableEvents;
    }

    /**
     * Set the end event.
     *
     * @param EndEvent $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(EndEvent $endEvent)
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
     * @return EndEvent|null The end event, or null if the call has not yet completed.
     */
    public function endEvent()
    {
        return $this->endEvent;
    }

    /**
     * Get all events as an array.
     *
     * @return array<Event> The events.
     */
    public function allEvents()
    {
        $events = $this->traversableEvents();

        if ($this->endEvent && $this->responseEvent !== $this->endEvent) {
            $events[] = $this->endEvent;
        }

        if ($this->responseEvent) {
            array_unshift($events, $this->responseEvent);
        }

        array_unshift($events, $this->calledEvent);

        return $events;
    }

    /**
     * Get all calls as an array.
     *
     * @return array<Call> The calls.
     */
    public function allCalls()
    {
        return array($this);
    }

    /**
     * Returns true if this call has responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return boolean True if this call has responded.
     */
    public function hasResponded()
    {
        return (boolean) $this->responseEvent;
    }

    /**
     * Returns true if this call has responded with a traversable.
     *
     * @return boolean True if this call has responded with a traversable.
     */
    public function isTraversable()
    {
        if (!$this->responseEvent instanceof ReturnedEvent) {
            return false;
        }

        $returnValue = $this->responseEvent->value();

        return is_array($returnValue) || $returnValue instanceof Traversable;
    }

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return boolean True if this call has responded with a generator.
     */
    public function isGenerator()
    {
        return $this->responseEvent instanceof ReturnedEvent &&
            $this->responseEvent->value() instanceof Generator;
    }

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
     * @return boolean True if this call has completed.
     */
    public function hasCompleted()
    {
        return (boolean) $this->endEvent;
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
     * @return Arguments The received arguments.
     */
    public function arguments()
    {
        return $this->calledEvent->arguments();
    }

    /**
     * Get an argument by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined, or no arguments were recorded.
     */
    public function argument($index = 0)
    {
        return $this->calledEvent->arguments()->get($index);
    }

    /**
     * Get the returned value.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value.
     */
    public function returnValue()
    {
        if ($this->responseEvent instanceof ReturnedEvent) {
            return $this->responseEvent->value();
        }

        throw new UndefinedResponseException(
            'The call has not yet returned a value.'
        );
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|Error            The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception.
     */
    public function exception()
    {
        if ($this->endEvent instanceof ThrewEvent) {
            return $this->endEvent->exception();
        }

        throw new UndefinedResponseException(
            'The call has not yet thrown an exception.'
        );
    }

    /**
     * Get the response.
     *
     * @return tuple<Exception|Error|null,mixed> A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException        If this call has not yet responded.
     */
    public function response()
    {
        if ($this->responseEvent instanceof ReturnedEvent) {
            return array(null, $this->responseEvent->value());
        }

        if ($this->endEvent instanceof ThrewEvent) {
            return array($this->endEvent->exception(), null);
        }

        throw new UndefinedResponseException('The call has not yet responded.');
    }

    /**
     * Get the time at which the call responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
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
    public function endTime()
    {
        if ($this->endEvent) {
            return $this->endEvent->time();
        }
    }

    private function normalizeIndex($size, $index, &$normalized = null)
    {
        $normalized = null;

        if ($index < 0) {
            $potential = $size + $index;

            if ($potential < 0) {
                return false;
            }
        } else {
            $potential = $index;
        }

        if ($potential >= $size) {
            return false;
        }

        $normalized = $potential;

        return true;
    }

    private $calledEvent;
    private $responseEvent;
    private $traversableEvents;
    private $endEvent;
}
