<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event;

use ArrayIterator;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Collection\NormalizesIndices;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Iterator;

/**
 * Represents a sequence of events.
 */
class EventSequence implements EventCollection
{
    use NormalizesIndices;

    /**
     * Construct a new event sequence.
     *
     * @param array<int,Event>    $events              The events.
     * @param CallVerifierFactory $callVerifierFactory The call verifier factory to use.
     */
    public function __construct(
        array $events,
        CallVerifierFactory $callVerifierFactory
    ) {
        $calls = [];

        foreach ($events as $event) {
            if ($event instanceof Call) {
                $calls[] = $event;
            }
        }

        $this->events = $events;
        $this->calls = $calls;
        $this->eventCount = count($events);
        $this->callCount = count($calls);
        $this->callVerifierFactory = $callVerifierFactory;
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return bool True if this collection contains any events.
     */
    public function hasEvents(): bool
    {
        return $this->eventCount > 0;
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return bool True if this collection contains any calls.
     */
    public function hasCalls(): bool
    {
        return $this->callCount > 0;
    }

    /**
     * Get the number of events.
     *
     * @return int The event count.
     */
    public function eventCount(): int
    {
        return $this->eventCount;
    }

    /**
     * Get the number of calls.
     *
     * @return int The call count.
     */
    public function callCount(): int
    {
        return $this->callCount;
    }

    /**
     * Get the event count.
     *
     * @return int The event count.
     */
    public function count(): int
    {
        return $this->eventCount;
    }

    /**
     * Get all events as an array.
     *
     * @return array<int,Event> The events.
     */
    public function allEvents(): array
    {
        return $this->events;
    }

    /**
     * Get all calls as an array.
     *
     * @return array<int,Call> The calls.
     */
    public function allCalls(): array
    {
        return $this->callVerifierFactory->fromCalls($this->calls);
    }

    /**
     * Get the first event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent(): Event
    {
        if (empty($this->events)) {
            throw new UndefinedEventException(0);
        }

        return $this->events[0];
    }

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent(): Event
    {
        if ($count = count($this->events)) {
            return $this->events[$count - 1];
        }

        throw new UndefinedEventException(0);
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
        if (!$this->normalizeIndex($this->eventCount, $index, $normalized)) {
            throw new UndefinedEventException($index);
        }

        return $this->events[$normalized];
    }

    /**
     * Get the first call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall(): Call
    {
        if (isset($this->calls[0])) {
            return $this->callVerifierFactory->fromCall($this->calls[0]);
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get the last call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall(): Call
    {
        if ($this->callCount) {
            return $this->callVerifierFactory
                ->fromCall($this->calls[$this->callCount - 1]);
        }

        throw new UndefinedCallException(0);
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
        if (!$this->normalizeIndex($this->callCount, $index, $normalized)) {
            throw new UndefinedCallException($index);
        }

        return $this->callVerifierFactory->fromCall($this->calls[$normalized]);
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator<int,Event> The iterator.
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->events);
    }

    /**
     * @var array<int,Event>
     */
    private $events;

    /**
     * @var array<int,Call>
     */
    private $calls;

    /**
     * @var int
     */
    private $eventCount;

    /**
     * @var int
     */
    private $callCount;

    /**
     * @var CallVerifierFactory
     */
    private $callVerifierFactory;
}
