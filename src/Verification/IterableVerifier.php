<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use ArrayIterator;
use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Event\ProducedEvent;
use Eloquent\Phony\Call\Event\UsedEvent;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Collection\NormalizesIndices;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Spy\Spy;
use Iterator;
use Throwable;

/**
 * Checks and asserts the behavior of iterables.
 */
class IterableVerifier implements EventCollection, CardinalityVerifier
{
    use CardinalityVerifierTrait;
    use NormalizesIndices;

    /**
     * Construct a new iterable verifier.
     *
     * @param Spy|Call            $subject             The subject.
     * @param array<int,Call>     $calls               The iterable calls.
     * @param MatcherFactory      $matcherFactory      The matcher factory to use.
     * @param CallVerifierFactory $callVerifierFactory The call verifier factory to use.
     * @param AssertionRecorder   $assertionRecorder   The assertion recorder to use.
     * @param AssertionRenderer   $assertionRenderer   The assertion renderer to use.
     */
    public function __construct(
        $subject,
        array $calls,
        MatcherFactory $matcherFactory,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer
    ) {
        $this->subject = $subject;
        $this->matcherFactory = $matcherFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->isGenerator = false;
        $this->events = $calls;
        $this->calls = $calls;
        $this->eventCount = $this->callCount = count($calls);
        $this->callVerifierFactory = $callVerifierFactory;
        $this->cardinality = new Cardinality();
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
        if ($this->eventCount < 1) {
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
        if ($this->eventCount) {
            return $this->events[$this->eventCount - 1];
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
     * @return Iterator<int,Call> The iterator.
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->allCalls());
    }

    /**
     * Checks if iteration of the subject commenced.
     *
     * @return ?EventCollection The result.
     */
    public function checkUsed(): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        if ($this->subject instanceof Call) {
            $cardinality->assertSingular();
        }

        $matchingEvents = [];
        $matchCount = 0;

        foreach ($this->calls as $call) {
            foreach ($call->iterableEvents() as $event) {
                if ($event instanceof UsedEvent) {
                    $matchingEvents[] = $event;
                    ++$matchCount;
                }
            }
        }

        if ($cardinality->matches($matchCount, $this->callCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless iteration of the subject commenced.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function used(): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkUsed()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderIterableUsed(
                $this->subject,
                $cardinality,
                $this->isGenerator
            )
        );
    }

    /**
     * Checks if the subject produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the subject
     * produced any value.
     *
     * With a single argument, it checks that a value matching the argument was
     * produced.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were produced together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return ?EventCollection The result.
     */
    public function checkProduced(
        $keyOrValue = null,
        $value = null
    ): ?EventCollection {
        $cardinality = $this->resetCardinality();
        $argumentCount = func_num_args();

        if (1 === $argumentCount) {
            $key = null;
            $value = $this->matcherFactory->adapt($keyOrValue);
        } elseif ($argumentCount > 0)  {
            $key = $this->matcherFactory->adapt($keyOrValue);
            $value = $this->matcherFactory->adapt($value);
        } else {
            $key = null;
            $value = null;
        }

        $isCall = $this->subject instanceof Call;
        $matchingEvents = [];
        $matchCount = 0;
        $eventCount = 0;

        foreach ($this->calls as $call) {
            $isMatchingCall = false;

            foreach ($call->iterableEvents() as $event) {
                if ($event instanceof ProducedEvent) {
                    ++$eventCount;

                    if ($key && !$key->matches($event->key())) {
                        continue;
                    }

                    if ($value && !$value->matches($event->value())) {
                        continue;
                    }

                    $matchingEvents[] = $event;
                    $isMatchingCall = true;

                    if ($isCall) {
                        ++$matchCount;
                    }
                }
            }

            if (!$isCall && $isMatchingCall) {
                ++$matchCount;
            }
        }

        if ($isCall) {
            $totalCount = $eventCount;
        } else {
            $totalCount = $this->callCount;
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless the subject produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the subject
     * produced any value.
     *
     * With a single argument, it checks that a value matching the argument was
     * produced.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were produced together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function produced(
        $keyOrValue = null,
        $value = null
    ): ?EventCollection {
        $cardinality = $this->cardinality;
        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $key = null;
            $arguments = [];
        } elseif (1 === $argumentCount) {
            $key = null;
            $value = $this->matcherFactory->adapt($keyOrValue);
            $arguments = [$value];
        } else {
            $key = $this->matcherFactory->adapt($keyOrValue);
            $value = $this->matcherFactory->adapt($value);
            $arguments = [$key, $value];
        }

        if ($result = $this->checkProduced(...$arguments)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderIterableProduced(
                $this->subject,
                $cardinality,
                $this->isGenerator,
                $key,
                $value
            )
        );
    }

    /**
     * Checks if the subject was completely consumed.
     *
     * @return ?EventCollection The result.
     */
    public function checkConsumed(): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        if ($this->subject instanceof Call) {
            $cardinality->assertSingular();
        }

        $matchingEvents = [];
        $matchCount = 0;

        foreach ($this->calls as $call) {
            if (!$endEvent = $call->endEvent()) {
                continue;
            }
            if (!$call->isIterable()) {
                continue;
            }

            ++$matchCount;
            $matchingEvents[] = $endEvent;
        }

        if ($cardinality->matches($matchCount, $this->callCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless the subject was completely consumed.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function consumed(): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkConsumed()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderIterableConsumed(
                $this->subject,
                $cardinality,
                $this->isGenerator
            )
        );
    }

    /**
     * @var Spy|Call
     */
    protected $subject;

    /**
     * @var MatcherFactory
     */
    protected $matcherFactory;

    /**
     * @var AssertionRecorder
     */
    protected $assertionRecorder;

    /**
     * @var AssertionRenderer
     */
    protected $assertionRenderer;

    /**
     * @var bool
     */
    protected $isGenerator;

    /**
     * @var array<int,Call>
     */
    protected $events;

    /**
     * @var array<int,Call>
     */
    protected $calls;

    /**
     * @var int
     */
    protected $eventCount;

    /**
     * @var int
     */
    protected $callCount;

    /**
     * @var CallVerifierFactory
     */
    protected $callVerifierFactory;
}
