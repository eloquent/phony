<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use ArrayIterator;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\EndEvent;
use Eloquent\Phony\Call\Event\IterableEvent;
use Eloquent\Phony\Call\Event\ResponseEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Collection\NormalizesIndices;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Generator;
use InvalidArgumentException;
use Iterator;
use Throwable;
use Traversable;

/**
 * Represents a single call.
 */
class CallData implements Call
{
    use NormalizesIndices;

    /**
     * A comparator for ordering calls by sequence number.
     *
     * @param Call $a The first call.
     * @param Call $b The second call.
     *
     * @return int The comparison value.
     */
    public static function compareSequential(Call $a, Call $b): int
    {
        return $a->sequenceNumber() <=> $b->sequenceNumber();
    }

    /**
     * Construct a new call data instance.
     *
     * @param int         $index       The index of this call.
     * @param CalledEvent $calledEvent The 'called' event.
     *
     * @throws InvalidArgumentException If the supplied calls respresent an invalid call state.
     */
    public function __construct(int $index, CalledEvent $calledEvent)
    {
        $this->index = $index;

        $calledEvent->setCall($this);
        $this->calledEvent = $calledEvent;

        $this->iterableEvents = [];
    }

    /**
     * Get the call index.
     *
     * This number tracks the order of this call with respect to other calls
     * made against the same spy.
     *
     * @return int The index.
     */
    public function index(): int
    {
        return $this->index;
    }

    /**
     * Get the sequence number.
     *
     * The sequence number is a unique number assigned to every event that Phony
     * records. The numbers are assigned sequentially, meaning that sequence
     * numbers can be used to determine event order.
     *
     * @return int The sequence number.
     */
    public function sequenceNumber(): int
    {
        return $this->calledEvent->sequenceNumber();
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time(): float
    {
        return $this->calledEvent->time();
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return bool True if this collection contains any events.
     */
    public function hasEvents(): bool
    {
        return true;
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return bool True if this collection contains any calls.
     */
    public function hasCalls(): bool
    {
        return true;
    }

    /**
     * Get the number of events.
     *
     * @return int The event count.
     */
    public function eventCount(): int
    {
        $events = $this->allEvents();

        return count($events);
    }

    /**
     * Get the number of calls.
     *
     * @return int The call count.
     */
    public function callCount(): int
    {
        return 1;
    }

    /**
     * Get the event count.
     *
     * @return int The event count.
     */
    public function count(): int
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
    public function firstEvent(): Event
    {
        return $this->calledEvent;
    }

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent(): Event
    {
        if ($this->endEvent) {
            return $this->endEvent;
        }

        if ($events = $this->iterableEvents()) {
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
     * @param int $index The index.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt(int $index = 0): Event
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
    public function firstCall(): Call
    {
        return $this;
    }

    /**
     * Get the last call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall(): Call
    {
        return $this;
    }

    /**
     * Get a call by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int $index The index.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt(int $index = 0): Call
    {
        if (0 === $index || -1 === $index) {
            return $this;
        }

        throw new UndefinedCallException($index);
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator<int,Call> The iterator.
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator([$this]);
    }

    /**
     * Get the 'called' event.
     *
     * @return CalledEvent The 'called' event.
     */
    public function calledEvent(): CalledEvent
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
    public function setResponseEvent(ResponseEvent $responseEvent): void
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
     * @return ?ResponseEvent The response event, or null if the call has not yet responded.
     */
    public function responseEvent(): ?ResponseEvent
    {
        return $this->responseEvent;
    }

    /**
     * Add an iterable event.
     *
     * @param IterableEvent $iterableEvent The iterable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addIterableEvent(IterableEvent $iterableEvent): void
    {
        if (!$this->isIterable()) {
            throw new InvalidArgumentException('Not an iterable call.');
        }
        if ($this->endEvent) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $iterableEvent->setCall($this);
        $this->iterableEvents[] = $iterableEvent;
    }

    /**
     * Get the iterable events.
     *
     * @return array<int,IterableEvent> The iterable events.
     */
    public function iterableEvents(): array
    {
        return $this->iterableEvents;
    }

    /**
     * Set the end event.
     *
     * @param EndEvent $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(EndEvent $endEvent): void
    {
        if ($this->endEvent) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $endEvent->setCall($this);
        $this->endEvent = $endEvent;
    }

    /**
     * Get the end event.
     *
     * @return ?EndEvent The end event, or null if the call has not yet completed.
     */
    public function endEvent(): ?EndEvent
    {
        return $this->endEvent;
    }

    /**
     * Get all events as an array.
     *
     * @return array<int,Event> The events.
     */
    public function allEvents(): array
    {
        $events = $this->iterableEvents();

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
     * @return array<int,Call> The calls.
     */
    public function allCalls(): array
    {
        return [$this];
    }

    /**
     * Returns true if this call has responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return bool True if this call has responded.
     */
    public function hasResponded(): bool
    {
        return (bool) $this->responseEvent;
    }

    /**
     * Returns true if this call has responded with an iterable.
     *
     * @return bool True if this call has responded with an iterable.
     */
    public function isIterable(): bool
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
     * @return bool True if this call has responded with a generator.
     */
    public function isGenerator(): bool
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
     * Similarly, when iterable spies are in use, a call that returns an
     * iterable will not be considered complete until the iterable has been
     * completely consumed via iteration.
     *
     * @return bool True if this call has completed.
     */
    public function hasCompleted(): bool
    {
        return (bool) $this->endEvent;
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback(): callable
    {
        return $this->calledEvent->callback();
    }

    /**
     * Get the received arguments.
     *
     * @return Arguments The received arguments.
     */
    public function arguments(): Arguments
    {
        return $this->calledEvent->arguments();
    }

    /**
     * Get an argument by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined, or no arguments were recorded.
     */
    public function argument(int $index = 0)
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
     * Get the value returned from the generator.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value via generator.
     */
    public function generatorReturnValue()
    {
        if ($this->endEvent instanceof ReturnedEvent) {
            return $this->endEvent->value();
        }

        throw new UndefinedResponseException(
            'The call has not yet returned a value via generator.'
        );
    }

    /**
     * Get the thrown exception.
     *
     * @return Throwable                  The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception.
     */
    public function exception(): Throwable
    {
        if ($this->responseEvent instanceof ThrewEvent) {
            return $this->responseEvent->exception();
        }

        throw new UndefinedResponseException(
            'The call has not yet thrown an exception.'
        );
    }

    /**
     * Get the exception thrown from the generator.
     *
     * @return Throwable                  The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception via generator.
     */
    public function generatorException(): Throwable
    {
        if ($this->endEvent instanceof ThrewEvent) {
            return $this->endEvent->exception();
        }

        throw new UndefinedResponseException(
            'The call has not yet thrown an exception via generator.'
        );
    }

    /**
     * Get the response.
     *
     * @return array{0:?Throwable,1:mixed} A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException  If this call has not yet responded.
     */
    public function response(): array
    {
        if ($this->responseEvent instanceof ReturnedEvent) {
            return [null, $this->responseEvent->value()];
        }

        if ($this->responseEvent instanceof ThrewEvent) {
            return [$this->responseEvent->exception(), null];
        }

        throw new UndefinedResponseException('The call has not yet responded.');
    }

    /**
     * Get the response from the generator.
     *
     * @return array{0:?Throwable,1:mixed} A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException  If this call has not yet responded via generator.
     */
    public function generatorResponse(): array
    {
        if ($this->endEvent instanceof ReturnedEvent) {
            return [null, $this->endEvent->value()];
        }

        if ($this->endEvent instanceof ThrewEvent) {
            return [$this->endEvent->exception(), null];
        }

        throw new UndefinedResponseException(
            'The call has not yet responded via generator.'
        );
    }

    /**
     * Get the time at which the call responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return ?float The time at which the call responded, in seconds since the Unix epoch, or null if the call has not yet responded.
     */
    public function responseTime(): ?float
    {
        if ($this->responseEvent) {
            return $this->responseEvent->time();
        }

        return null;
    }

    /**
     * Get the time at which the call completed.
     *
     * When generator spies are in use, a call that returns a generator will not
     * be considered complete until the generator has been completely consumed
     * via iteration.
     *
     * Similarly, when iterable spies are in use, a call that returns an
     * iterable will not be considered complete until the iterable has been
     * completely consumed via iteration.
     *
     * @return ?float The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime(): ?float
    {
        if ($this->endEvent) {
            return $this->endEvent->time();
        }

        return null;
    }

    /**
     * @var int
     */
    private $index;

    /**
     * @var CalledEvent
     */
    private $calledEvent;

    /**
     * @var ?ResponseEvent
     */
    private $responseEvent;

    /**
     * @var array<int,IterableEvent>
     */
    private $iterableEvents;

    /**
     * @var ?EndEvent
     */
    private $endEvent;
}
