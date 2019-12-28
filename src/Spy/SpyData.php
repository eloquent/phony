<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Collection\NormalizesIndices;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\WrappedInvocableTrait;
use Generator;
use Iterator;
use Throwable;
use Traversable;

/**
 * Spies on a function or method.
 */
class SpyData implements Spy
{
    use NormalizesIndices;
    use WrappedInvocableTrait;

    /**
     * Construct a new spy.
     *
     * @param ?callable           $callback            The callback, or null to create an anonymous spy.
     * @param string              $label               The label.
     * @param CallFactory         $callFactory         The call factory to use.
     * @param Invoker             $invoker             The invoker to use.
     * @param GeneratorSpyFactory $generatorSpyFactory The generator spy factory to use.
     * @param IterableSpyFactory  $iterableSpyFactory  The iterable spy factory to use.
     */
    public function __construct(
        ?callable $callback,
        string $label,
        CallFactory $callFactory,
        Invoker $invoker,
        GeneratorSpyFactory $generatorSpyFactory,
        IterableSpyFactory $iterableSpyFactory
    ) {
        if (!$callback) {
            $this->isAnonymous = true;
            $this->callback = function () {};
        } else {
            $this->isAnonymous = false;
            $this->callback = $callback;
        }

        $this->label = $label;
        $this->callFactory = $callFactory;
        $this->invoker = $invoker;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->iterableSpyFactory = $iterableSpyFactory;

        $this->calls = [];
        $this->useGeneratorSpies = true;
        $this->useIterableSpies = false;
        $this->isRecording = true;
    }

    /**
     * Get the next call index.
     *
     * @return int The index.
     */
    public function nextIndex(): int
    {
        return count($this->calls);
    }

    /**
     * Turn on or off the use of generator spies.
     *
     * @param bool $useGeneratorSpies True to use generator spies.
     *
     * @return $this This spy.
     */
    public function setUseGeneratorSpies(bool $useGeneratorSpies): Spy
    {
        $this->useGeneratorSpies = $useGeneratorSpies;

        return $this;
    }

    /**
     * Returns true if this spy uses generator spies.
     *
     * @return bool True if this spy uses generator spies.
     */
    public function useGeneratorSpies(): bool
    {
        return $this->useGeneratorSpies;
    }

    /**
     * Turn on or off the use of iterable spies.
     *
     * @param bool $useIterableSpies True to use iterable spies.
     *
     * @return $this This spy.
     */
    public function setUseIterableSpies(bool $useIterableSpies): Spy
    {
        $this->useIterableSpies = $useIterableSpies;

        return $this;
    }

    /**
     * Returns true if this spy uses iterable spies.
     *
     * @return bool True if this spy uses iterable spies.
     */
    public function useIterableSpies(): bool
    {
        return $this->useIterableSpies;
    }

    /**
     * Stop recording calls.
     *
     * @return $this This spy.
     */
    public function stopRecording(): Spy
    {
        $this->isRecording = false;

        return $this;
    }

    /**
     * Start recording calls.
     *
     * @return $this This spy.
     */
    public function startRecording(): Spy
    {
        $this->isRecording = true;

        return $this;
    }

    /**
     * Set the calls.
     *
     * @param array<int,Call> $calls The calls.
     */
    public function setCalls(array $calls): void
    {
        $this->calls = $calls;
    }

    /**
     * Add a call.
     *
     * @param Call $call The call.
     */
    public function addCall(Call $call): void
    {
        $this->calls[] = $call;
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return bool True if this collection contains any events.
     */
    public function hasEvents(): bool
    {
        return (bool) $this->calls;
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return bool True if this collection contains any calls.
     */
    public function hasCalls(): bool
    {
        return (bool) $this->calls;
    }

    /**
     * Get the number of events.
     *
     * @return int The event count.
     */
    public function eventCount(): int
    {
        return count($this->calls);
    }

    /**
     * Get the number of calls.
     *
     * @return int The call count.
     */
    public function callCount(): int
    {
        return count($this->calls);
    }

    /**
     * Get the event count.
     *
     * @return int The event count.
     */
    public function count(): int
    {
        return count($this->calls);
    }

    /**
     * Get all events as an array.
     *
     * @return array<int,Event> The events.
     */
    public function allEvents(): array
    {
        return $this->calls;
    }

    /**
     * Get all calls as an array.
     *
     * @return array<int,Call> The calls.
     */
    public function allCalls(): array
    {
        return $this->calls;
    }

    /**
     * Get the first event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent(): Event
    {
        if (empty($this->calls)) {
            throw new UndefinedEventException(0);
        }

        return $this->calls[0];
    }

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent(): Event
    {
        if ($count = count($this->calls)) {
            return $this->calls[$count - 1];
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
        if (!$this->normalizeIndex(count($this->calls), $index, $normalized)) {
            throw new UndefinedEventException($index);
        }

        return $this->calls[$normalized];
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
            return $this->calls[0];
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
        if ($count = count($this->calls)) {
            return $this->calls[$count - 1];
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
        if (!$this->normalizeIndex(count($this->calls), $index, $normalized)) {
            throw new UndefinedCallException($index);
        }

        return $this->calls[$normalized];
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator<int,Call> The iterator.
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->calls);
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array<int,mixed> $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invokeWith($arguments = [])
    {
        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        /** @var callable */
        $callback = $this->callback;

        if (!$this->isRecording) {
            return $this->invoker->callWith($callback, $arguments);
        }

        $call = $this->callFactory->record($callback, $arguments, $this);
        $responseEvent = $call->responseEvent();

        if ($responseEvent instanceof ThrewEvent) {
            $call->setEndEvent($responseEvent);

            throw $responseEvent->exception();
        }

        /** @var ReturnedEvent */
        $returnedEvent = $responseEvent;
        $returnValue = $returnedEvent->value();

        if ($this->useGeneratorSpies && $returnValue instanceof Generator) {
            return $this->generatorSpyFactory->create($call, $returnValue);
        }

        if (
            $this->useIterableSpies &&
            (is_array($returnValue) || $returnValue instanceof Traversable)
        ) {
            return $this->iterableSpyFactory->create($call, $returnValue);
        }

        $call->setEndEvent($returnedEvent);

        return $returnValue;
    }

    /**
     * Limits the output displayed when `var_dump` is used.
     *
     * @return array<string,mixed> The contents to export.
     */
    public function __debugInfo(): array
    {
        return ['label' => $this->label];
    }

    /**
     * @var CallFactory
     */
    private $callFactory;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var GeneratorSpyFactory
     */
    private $generatorSpyFactory;

    /**
     * @var IterableSpyFactory
     */
    private $iterableSpyFactory;

    /**
     * @var bool
     */
    private $useGeneratorSpies;

    /**
     * @var bool
     */
    private $useIterableSpies;

    /**
     * @var bool
     */
    private $isRecording;

    /**
     * @var array<int,Call>
     */
    private $calls;
}
