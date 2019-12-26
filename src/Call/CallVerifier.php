<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\EndEvent;
use Eloquent\Phony\Call\Event\IterableEvent;
use Eloquent\Phony\Call\Event\ResponseEvent;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Verification\Cardinality;
use Eloquent\Phony\Verification\CardinalityVerifier;
use Eloquent\Phony\Verification\CardinalityVerifierTrait;
use Eloquent\Phony\Verification\GeneratorVerifier;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifier;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use Generator;
use InvalidArgumentException;
use Iterator;
use Throwable;
use Traversable;

/**
 * Provides convenience methods for verifying the details of a call.
 */
class CallVerifier implements Call, CardinalityVerifier
{
    use CardinalityVerifierTrait;

    /**
     * Construct a new call verifier.
     *
     * @param Call                     $call                     The call.
     * @param MatcherFactory           $matcherFactory           The matcher factory to use.
     * @param MatcherVerifier          $matcherVerifier          The matcher verifier to use.
     * @param GeneratorVerifierFactory $generatorVerifierFactory The generator verifier factory to use.
     * @param IterableVerifierFactory  $iterableVerifierFactory  The iterable verifier factory to use.
     * @param AssertionRecorder        $assertionRecorder        The assertion recorder to use.
     * @param AssertionRenderer        $assertionRenderer        The assertion renderer to use.
     */
    public function __construct(
        Call $call,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        IterableVerifierFactory $iterableVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer
    ) {
        $this->call = $call;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->generatorVerifierFactory = $generatorVerifierFactory;
        $this->iterableVerifierFactory = $iterableVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->cardinality = new Cardinality();

        $this->argumentCount = count($call->arguments());
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
        return $this->call->index();
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
        return $this->call->sequenceNumber();
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time(): float
    {
        return $this->call->time();
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return bool True if this collection contains any events.
     */
    public function hasEvents(): bool
    {
        return $this->call->hasEvents();
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return bool True if this collection contains any calls.
     */
    public function hasCalls(): bool
    {
        return $this->call->hasCalls();
    }

    /**
     * Get the number of events.
     *
     * @return int The event count.
     */
    public function eventCount(): int
    {
        return $this->call->eventCount();
    }

    /**
     * Get the number of calls.
     *
     * @return int The call count.
     */
    public function callCount(): int
    {
        return $this->call->callCount();
    }

    /**
     * Get the event count.
     *
     * @return int The event count.
     */
    public function count(): int
    {
        return $this->call->count();
    }

    /**
     * Get the first event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent(): Event
    {
        return $this->call->firstEvent();
    }

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent(): Event
    {
        return $this->call->lastEvent();
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
        return $this->call->eventAt($index);
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
        /** @var Iterator<int,Call> */
        $iterator = $this->call->getIterator();

        return $iterator;
    }

    /**
     * Get the 'called' event.
     *
     * @return CalledEvent The 'called' event.
     */
    public function calledEvent(): CalledEvent
    {
        return $this->call->calledEvent();
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
        $this->call->setResponseEvent($responseEvent);
    }

    /**
     * Get the response event.
     *
     * @return ?ResponseEvent The response event, or null if the call has not yet responded.
     */
    public function responseEvent(): ?ResponseEvent
    {
        return $this->call->responseEvent();
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
        $this->call->addIterableEvent($iterableEvent);
    }

    /**
     * Get the iterable events.
     *
     * @return array<int,IterableEvent> The iterable events.
     */
    public function iterableEvents(): array
    {
        return $this->call->iterableEvents();
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
        $this->call->setEndEvent($endEvent);
    }

    /**
     * Get the end event.
     *
     * @return ?EndEvent The end event, or null if the call has not yet completed.
     */
    public function endEvent(): ?EndEvent
    {
        return $this->call->endEvent();
    }

    /**
     * Get all events as an array.
     *
     * @return array<int,Event> The events.
     */
    public function allEvents(): array
    {
        return $this->call->allEvents();
    }

    /**
     * Get all calls as an array.
     *
     * @return array<int,Call> The calls.
     */
    public function allCalls(): array
    {
        return $this->call->allCalls();
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
        return $this->call->hasResponded();
    }

    /**
     * Returns true if this call has responded with an iterable.
     *
     * @return bool True if this call has responded with an iterable.
     */
    public function isIterable(): bool
    {
        return $this->call->isIterable();
    }

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return bool True if this call has responded with a generator.
     */
    public function isGenerator(): bool
    {
        return $this->call->isGenerator();
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
        return $this->call->hasCompleted();
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback(): callable
    {
        return $this->call->callback();
    }

    /**
     * Get the received arguments.
     *
     * @return Arguments The received arguments.
     */
    public function arguments(): Arguments
    {
        return $this->call->arguments();
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
        return $this->call->argument($index);
    }

    /**
     * Get the returned value.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value.
     */
    public function returnValue()
    {
        return $this->call->returnValue();
    }

    /**
     * Get the value returned from the generator.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value via generator.
     */
    public function generatorReturnValue()
    {
        return $this->call->generatorReturnValue();
    }

    /**
     * Get the thrown exception.
     *
     * @return Throwable                  The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception.
     */
    public function exception(): Throwable
    {
        return $this->call->exception();
    }

    /**
     * Get the exception thrown from the generator.
     *
     * @return Throwable                  The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception via generator.
     */
    public function generatorException(): Throwable
    {
        return $this->call->generatorException();
    }

    /**
     * Get the response.
     *
     * @return array{0:?Throwable,1:mixed} A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException  If this call has not yet responded.
     */
    public function response(): array
    {
        return $this->call->response();
    }

    /**
     * Get the response from the generator.
     *
     * @return array{0:?Throwable,1:mixed} A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException  If this call has not yet responded via generator.
     */
    public function generatorResponse(): array
    {
        return $this->call->generatorResponse();
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
        return $this->call->responseTime();
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
        return $this->call->endTime();
    }

    /**
     * Get the call duration.
     *
     * @return ?float The call duration in seconds, or null if the call has not yet completed.
     */
    public function duration(): ?float
    {
        $endTime = $this->call->endTime();

        if (null === $endTime) {
            return null;
        }

        return $endTime - $this->call->time();
    }

    /**
     * Get the call response duration.
     *
     * @return ?float The call response duration in seconds, or null if the call has not yet responded.
     */
    public function responseDuration(): ?float
    {
        $responseTime = $this->call->responseTime();

        if (null === $responseTime) {
            return null;
        }

        return $responseTime - $this->call->time();
    }

    /**
     * Get the number of arguments.
     *
     * @return int The number of arguments.
     */
    public function argumentCount(): int
    {
        return $this->argumentCount;
    }

    /**
     * Checks if called with the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return ?EventCollection            The result.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function checkCalledWith(...$arguments): ?EventCollection
    {
        $cardinality = $this->resetCardinality()->assertSingular();
        $matchers = $this->matcherFactory->adaptAll($arguments);

        list($matchCount, $matchingEvents) = $this->matchIf(
            $this->call,
            $this->matcherVerifier
                ->matches($matchers, $this->call->arguments()->all())
        );

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless called with the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return ?EventCollection            The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws Throwable                   If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledWith(...$arguments): ?EventCollection
    {
        $cardinality = $this->cardinality;
        $matchers = $this->matcherFactory->adaptAll($arguments);

        if ($result = $this->checkCalledWith(...$matchers)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer
                ->renderCalledWith($this->call, $cardinality, $matchers)
        );
    }

    /**
     * Checks if this call responded.
     *
     * @return ?EventCollection            The result.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function checkResponded(): ?EventCollection
    {
        $cardinality = $this->resetCardinality()->assertSingular();
        $responseEvent = $this->call->responseEvent();

        list($matchCount, $matchingEvents) =
            $this->matchIf($responseEvent, $responseEvent);

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless this call responded.
     *
     * @return ?EventCollection            The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws Throwable                   If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function responded(): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkResponded()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderResponded($this->call, $cardinality)
        );
    }

    /**
     * Checks if this call completed.
     *
     * @return ?EventCollection            The result.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function checkCompleted(): ?EventCollection
    {
        $cardinality = $this->resetCardinality()->assertSingular();
        $endEvent = $this->call->endEvent();

        list($matchCount, $matchingEvents) =
            $this->matchIf($endEvent, $endEvent);

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless this call completed.
     *
     * @return ?EventCollection            The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws Throwable                   If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function completed(): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkCompleted()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderCompleted($this->call, $cardinality)
        );
    }

    /**
     * Checks if this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection            The result.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function checkReturned($value = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        if ($responseEvent = $this->call->responseEvent()) {
            list($exception, $returnValue) = $this->call->response();

            $hasReturned = !$exception;
        } else {
            $returnValue = null;
            $hasReturned = false;
        }

        if (0 === func_num_args()) {
            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, $hasReturned);

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            return null;
        }

        $value = $this->matcherFactory->adapt($value);

        list($matchCount, $matchingEvents) = $this->matchIf(
            $responseEvent,
            $hasReturned && $value->matches($returnValue)
        );

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection            The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws Throwable                   If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function returned($value = null): ?EventCollection
    {
        $cardinality = $this->cardinality;
        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = [];
        } else {
            $value = $this->matcherFactory->adapt($value);
            $arguments = [$value];
        }

        if ($result = $this->checkReturned(...$arguments)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer
                ->renderReturned($this->call, $cardinality, $value)
        );
    }

    /**
     * Checks if an exception of the supplied type was thrown.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection            The result.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws InvalidArgumentException    If the type is invalid.
     */
    public function checkThrew($type = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        if ($responseEvent = $this->call->responseEvent()) {
            list($exception) = $this->call->response();
        } else {
            $exception = null;
        }

        $isTypeSupported = false;

        if (!$type) {
            $isTypeSupported = true;

            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, $exception);

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            /** @var Throwable */
            $throwableException = $exception;

            list($matchCount, $matchingEvents) = $this->matchIf(
                $responseEvent,
                is_a($throwableException, $type)
            );

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }
        } elseif (is_object($type)) {
            if ($type instanceof InstanceHandle) {
                /** @var Matcher */
                $type = $type->get();
            }

            if ($type instanceof Throwable) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->equalTo($type, true);
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);
            }

            if ($isTypeSupported) {
                list($matchCount, $matchingEvents) = $this->matchIf(
                    $responseEvent,
                    $exception && $type->matches($exception)
                );

                if ($cardinality->matches($matchCount, 1)) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }
            }
        }

        if (!$isTypeSupported) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to match exceptions against %s.',
                    $this->assertionRenderer->renderValue($type)
                )
            );
        }

        return null;
    }

    /**
     * Throws an exception unless this call threw an exception of the supplied
     * type.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection            The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws InvalidArgumentException    If the type is invalid.
     * @throws Throwable                   If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($type instanceof InstanceHandle) {
            /** @var Throwable  */
            $type = $type->get();
        }

        if ($type instanceof Throwable) {
            $type = $this->matcherFactory->equalTo($type, true);
        } elseif ($this->matcherFactory->isMatcher($type)) {
            $type = $this->matcherFactory->adapt($type);
        }

        if ($result = $this->checkThrew($type)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer
                ->renderThrew($this->call, $cardinality, $type)
        );
    }

    /**
     * Checks if this call returned a generator.
     *
     * @return ?GeneratorVerifier          The result.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function checkGenerated(): ?GeneratorVerifier
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        if ($this->call->responseEvent()) {
            list(, $returnValue) = $this->call->response();

            $isMatch = $returnValue instanceof Generator;
        } else {
            $isMatch = false;
        }

        /** @var array<int,Call> $matchingEvents */
        list($matchCount, $matchingEvents) =
            $this->matchIf($this->call, $isMatch);

        if ($cardinality->matches($matchCount, 1)) {
            /** @var GeneratorVerifier */
            $verifier = $this->assertionRecorder->createSuccessFromEventCollection(
                $this->generatorVerifierFactory
                    ->create($this->call, $matchingEvents)
            );

            return $verifier;
        }

        return null;
    }

    /**
     * Throws an exception unless this call returned a generator.
     *
     * @return GeneratorVerifier           The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws Throwable                   If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function generated(): ?GeneratorVerifier
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkGenerated()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderGenerated($this->call, $cardinality)
        );
    }

    /**
     * Checks if this call returned an iterable.
     *
     * @return ?IterableVerifier           The result.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function checkIterated(): ?IterableVerifier
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        if ($this->call->responseEvent()) {
            list(, $returnValue) = $this->call->response();

            $isMatch =
                $returnValue instanceof Traversable || is_array($returnValue);
        } else {
            $isMatch = false;
        }

        /** @var array<int,Call> $matchingEvents */
        list($matchCount, $matchingEvents) =
            $this->matchIf($this->call, $isMatch);

        if ($cardinality->matches($matchCount, 1)) {
            /** @var IterableVerifier */
            $verifier = $this->assertionRecorder->createSuccessFromEventCollection(
                $this->iterableVerifierFactory
                    ->create($this->call, $matchingEvents)
            );

            return $verifier;
        }

        return null;
    }

    /**
     * Throws an exception unless this call returned an iterable.
     *
     * @return IterableVerifier            The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     * @throws Throwable                   If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function iterated(): ?IterableVerifier
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkIterated()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderIterated($this->call, $cardinality)
        );
    }

    /**
     * @param ?Event $event
     * @param mixed  $checkResult
     *
     * @return array{0:int,1:array<int,Event>}
     */
    private function matchIf(?Event $event, $checkResult): array
    {
        if ($event && $checkResult) {
            $matchCount = 1;
            $matchingEvents = [$event];
        } else {
            $matchCount = 0;
            $matchingEvents = [];
        }

        return [$matchCount, $matchingEvents];
    }

    /**
     * @var Call
     */
    private $call;

    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * @var MatcherVerifier
     */
    private $matcherVerifier;

    /**
     * @var GeneratorVerifierFactory
     */
    private $generatorVerifierFactory;

    /**
     * @var IterableVerifierFactory
     */
    private $iterableVerifierFactory;

    /**
     * @var AssertionRecorder
     */
    private $assertionRecorder;

    /**
     * @var AssertionRenderer
     */
    private $assertionRenderer;

    /**
     * @var int
     */
    private $argumentCount;
}
