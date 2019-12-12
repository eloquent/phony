<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event;

use Countable;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use IteratorAggregate;

/**
 * The interface implemented by event collections.
 *
 * @extends IteratorAggregate<int,Event>
 */
interface EventCollection extends IteratorAggregate, Countable
{
    /**
     * Returns true if this collection contains any events.
     *
     * @return bool True if this collection contains any events.
     */
    public function hasEvents(): bool;

    /**
     * Returns true if this collection contains any calls.
     *
     * @return bool True if this collection contains any calls.
     */
    public function hasCalls(): bool;

    /**
     * Get the number of events.
     *
     * @return int The event count.
     */
    public function eventCount(): int;

    /**
     * Get the number of calls.
     *
     * @return int The call count.
     */
    public function callCount(): int;

    /**
     * Get all events as an array.
     *
     * @return array<int,Event> The events.
     */
    public function allEvents(): array;

    /**
     * Get all calls as an array.
     *
     * @return array<int,Call> The calls.
     */
    public function allCalls(): array;

    /**
     * Get the first event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent(): Event;

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent(): Event;

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
    public function eventAt(int $index = 0): Event;

    /**
     * Get the first call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall(): Call;

    /**
     * Get the last call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall(): Call;

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
    public function callAt(int $index = 0): Call;
}
