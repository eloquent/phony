<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\EndEvent;
use Eloquent\Phony\Call\Event\IterableEvent;
use Eloquent\Phony\Call\Event\ResponseEvent;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use InvalidArgumentException;
use Throwable;

/**
 * The interface implemented by calls.
 */
interface Call extends Event, EventCollection
{
    /**
     * Get the call index.
     *
     * This number tracks the order of this call with respect to other calls
     * made against the same spy.
     *
     * @return int The index.
     */
    public function index(): int;

    /**
     * Returns true if this call has responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return bool True if this call has responded.
     */
    public function hasResponded(): bool;

    /**
     * Returns true if this call has responded with an iterable.
     *
     * @return bool True if this call has responded with an iterable.
     */
    public function isIterable(): bool;

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return bool True if this call has responded with a generator.
     */
    public function isGenerator(): bool;

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
    public function hasCompleted(): bool;

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback(): callable;

    /**
     * Get the arguments.
     *
     * @return Arguments The arguments.
     */
    public function arguments(): Arguments;

    /**
     * Get an argument by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function argument(int $index = 0);

    /**
     * Get the returned value.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value.
     */
    public function returnValue();

    /**
     * Get the value returned from the generator.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value via generator.
     */
    public function generatorReturnValue();

    /**
     * Get the thrown exception.
     *
     * @return Throwable                  The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception.
     */
    public function exception(): Throwable;

    /**
     * Get the exception thrown from the generator.
     *
     * @return Throwable                  The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception via generator.
     */
    public function generatorException(): Throwable;

    /**
     * Get the response.
     *
     * @return array{0:?Throwable,1:mixed} A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException  If this call has not yet responded.
     */
    public function response(): array;

    /**
     * Get the response from the generator.
     *
     * @return array{0:?Throwable,1:mixed} A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException  If this call has not yet responded via generator.
     */
    public function generatorResponse(): array;

    /**
     * Get the time at which the call responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return ?float The time at which the call responded, in seconds since the Unix epoch, or null if the call has not yet responded.
     */
    public function responseTime(): ?float;

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
    public function endTime(): ?float;

    /**
     * Get the 'called' event.
     *
     * @return CalledEvent The 'called' event.
     */
    public function calledEvent(): CalledEvent;

    /**
     * Set the response event.
     *
     * @param ResponseEvent $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already responded.
     */
    public function setResponseEvent(ResponseEvent $responseEvent): void;

    /**
     * Get the response event.
     *
     * @return ?ResponseEvent The response event, or null if the call has not yet responded.
     */
    public function responseEvent(): ?ResponseEvent;

    /**
     * Add an iterable event.
     *
     * @param IterableEvent $iterableEvent The iterable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addIterableEvent(IterableEvent $iterableEvent): void;

    /**
     * Get the iterable events.
     *
     * @return array<int,IterableEvent> The iterable events.
     */
    public function iterableEvents(): array;

    /**
     * Set the end event.
     *
     * @param EndEvent $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(EndEvent $endEvent): void;

    /**
     * Get the end event.
     *
     * @return ?EndEvent The end event, or null if the call has not yet completed.
     */
    public function endEvent(): ?EndEvent;
}
