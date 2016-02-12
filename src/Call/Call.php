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
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\EndEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Call\Event\TraversableEventInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Collection\Exception\UndefinedIndexException;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Collection\IndexNormalizerInterface;
use Eloquent\Phony\Event\EventInterface;
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
class Call implements CallInterface
{
    /**
     * Construct a new call.
     *
     * @param CalledEventInterface                  $calledEvent       The 'called' event.
     * @param ResponseEventInterface|null           $responseEvent     The response event, or null if the call has not yet responded.
     * @param array<TraversableEventInterface>|null $traversableEvents The traversable events.
     * @param EndEventInterface|null                $endEvent          The end event, or null if the call has not yet completed.
     * @param IndexNormalizerInterface|null         $indexNormalizer   The index normalizer to use.
     *
     * @throws InvalidArgumentException If the supplied calls respresent an invalid call state.
     */
    public function __construct(
        CalledEventInterface $calledEvent,
        ResponseEventInterface $responseEvent = null,
        array $traversableEvents = null,
        EndEventInterface $endEvent = null,
        IndexNormalizerInterface $indexNormalizer = null
    ) {
        if (null === $indexNormalizer) {
            $indexNormalizer = IndexNormalizer::instance();
        }

        $this->indexNormalizer = $indexNormalizer;

        $calledEvent->setCall($this);
        $this->calledEvent = $calledEvent;

        $this->traversableEvents = array();

        if ($responseEvent) {
            $this->setResponseEvent($responseEvent);
        }

        if (null !== $traversableEvents) {
            foreach ($traversableEvents as $traversableEvent) {
                $this->addTraversableEvent($traversableEvent);
            }
        }

        if ($endEvent) {
            $this->setEndEvent($endEvent);
        }
    }

    /**
     * Get the index normalizer.
     *
     * @return IndexNormalizerInterface The index normalizer.
     */
    public function indexNormalizer()
    {
        return $this->indexNormalizer;
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
     * @return EventInterface          The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent()
    {
        return $this->calledEvent;
    }

    /**
     * Get the last event.
     *
     * @return EventInterface          The event.
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
     * @return EventInterface          The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt($index = 0)
    {
        if (0 === $index) {
            return $this->calledEvent;
        }

        $events = $this->allEvents();
        $count = count($events);

        try {
            $normalized = $this->indexNormalizer->normalize($count, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedEventException($index, $e);
        }

        return $events[$normalized];
    }

    /**
     * Get the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
        return $this;
    }

    /**
     * Get the last call.
     *
     * @return CallInterface          The call.
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
     * @return CallInterface          The call.
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
     * Add a traversable event.
     *
     * @param TraversableEventInterface $traversableEvent The traversable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addTraversableEvent(
        TraversableEventInterface $traversableEvent
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
     * @return array<TraversableEventInterface> The traversable events.
     */
    public function traversableEvents()
    {
        return $this->traversableEvents;
    }

    /**
     * Set the end event.
     *
     * @param EndEventInterface $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(EndEventInterface $endEvent)
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
     * @return EndEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent()
    {
        return $this->endEvent;
    }

    /**
     * Get all events as an array.
     *
     * @return array<EventInterface> The events.
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
     * @return array<CallInterface> The calls.
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
        if (!$this->responseEvent instanceof ReturnedEventInterface) {
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
        return $this->responseEvent instanceof ReturnedEventInterface &&
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
     * @return ArgumentsInterface The received arguments.
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
        if ($this->responseEvent instanceof ReturnedEventInterface) {
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
        if ($this->endEvent instanceof ThrewEventInterface) {
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
        if ($this->responseEvent instanceof ReturnedEventInterface) {
            return array(null, $this->responseEvent->value());
        }

        if ($this->endEvent instanceof ThrewEventInterface) {
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

    private $calledEvent;
    private $responseEvent;
    private $traversableEvents;
    private $endEvent;
    private $indexNormalizer;
}
