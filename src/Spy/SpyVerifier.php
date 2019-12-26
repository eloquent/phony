<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallVerifier;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Invocation\WrappedInvocable;
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
 * Provides convenience methods for verifying interactions with a spy.
 */
class SpyVerifier implements Spy, CardinalityVerifier
{
    use CardinalityVerifierTrait;

    /**
     * Construct a new spy verifier.
     *
     * @param Spy                      $spy                      The spy.
     * @param MatcherFactory           $matcherFactory           The matcher factory to use.
     * @param MatcherVerifier          $matcherVerifier          The macther verifier to use.
     * @param GeneratorVerifierFactory $generatorVerifierFactory The generator verifier factory to use.
     * @param IterableVerifierFactory  $iterableVerifierFactory  The iterable verifier factory to use.
     * @param CallVerifierFactory      $callVerifierFactory      The call verifier factory to use.
     * @param AssertionRecorder        $assertionRecorder        The assertion recorder to use.
     * @param AssertionRenderer        $assertionRenderer        The assertion renderer to use.
     */
    public function __construct(
        Spy $spy,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        IterableVerifierFactory $iterableVerifierFactory,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer
    ) {
        $this->spy = $spy;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->generatorVerifierFactory = $generatorVerifierFactory;
        $this->iterableVerifierFactory = $iterableVerifierFactory;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->cardinality = new Cardinality();
    }

    /**
     * Get the spy.
     *
     * @return Spy The spy.
     */
    public function spy(): Spy
    {
        return $this->spy;
    }

    /**
     * Returns true if anonymous.
     *
     * @return bool True if anonymous.
     */
    public function isAnonymous(): bool
    {
        return $this->spy->isAnonymous();
    }

    /**
     * Get the callback.
     *
     * @return callable
     */
    public function callback(): ?callable
    {
        return $this->spy->callback();
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
        $this->spy->setUseGeneratorSpies($useGeneratorSpies);

        return $this;
    }

    /**
     * Returns true if this spy uses generator spies.
     *
     * @return bool True if this spy uses generator spies.
     */
    public function useGeneratorSpies(): bool
    {
        return $this->spy->useGeneratorSpies();
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
        $this->spy->setUseIterableSpies($useIterableSpies);

        return $this;
    }

    /**
     * Returns true if this spy uses iterable spies.
     *
     * @return bool True if this spy uses iterable spies.
     */
    public function useIterableSpies(): bool
    {
        return $this->spy->useIterableSpies();
    }

    /**
     * Set the label.
     *
     * @param string $label The label.
     *
     * @return $this This invocable.
     */
    public function setLabel(string $label): WrappedInvocable
    {
        $this->spy->setLabel($label);

        return $this;
    }

    /**
     * Get the label.
     *
     * @return string The label.
     */
    public function label(): string
    {
        return $this->spy->label();
    }

    /**
     * Stop recording calls.
     *
     * @return $this This spy.
     */
    public function stopRecording(): Spy
    {
        $this->spy->stopRecording();

        return $this;
    }

    /**
     * Start recording calls.
     *
     * @return $this This spy.
     */
    public function startRecording(): Spy
    {
        $this->spy->startRecording();

        return $this;
    }

    /**
     * Set the calls.
     *
     * @param array<int,Call> $calls The calls.
     */
    public function setCalls(array $calls): void
    {
        $this->spy->setCalls($calls);
    }

    /**
     * Add a call.
     *
     * @param Call $call The call.
     */
    public function addCall(Call $call): void
    {
        $this->spy->addCall($call);
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return bool True if this collection contains any events.
     */
    public function hasEvents(): bool
    {
        return $this->spy->hasEvents();
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return bool True if this collection contains any calls.
     */
    public function hasCalls(): bool
    {
        return $this->spy->hasCalls();
    }

    /**
     * Get the number of events.
     *
     * @return int The event count.
     */
    public function eventCount(): int
    {
        return $this->spy->eventCount();
    }

    /**
     * Get the number of calls.
     *
     * @return int The call count.
     */
    public function callCount(): int
    {
        return $this->spy->callCount();
    }

    /**
     * Get all events as an array.
     *
     * @return array<int,Event> The events.
     */
    public function allEvents(): array
    {
        return $this->spy->allEvents();
    }

    /**
     * Get all calls as an array.
     *
     * @return array<int,CallVerifier> The calls.
     */
    public function allCalls(): array
    {
        return $this->callVerifierFactory->fromCalls($this->spy->allCalls());
    }

    /**
     * Get the first event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent(): Event
    {
        return $this->spy->firstEvent();
    }

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent(): Event
    {
        return $this->spy->lastEvent();
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
        return $this->spy->eventAt($index);
    }

    /**
     * Get the first call.
     *
     * @return CallVerifier           The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall(): Call
    {
        return $this->callVerifierFactory->fromCall($this->spy->firstCall());
    }

    /**
     * Get the last call.
     *
     * @return CallVerifier           The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall(): Call
    {
        return $this->callVerifierFactory->fromCall($this->spy->lastCall());
    }

    /**
     * Get a call by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int $index The index.
     *
     * @return CallVerifier           The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt(int $index = 0): Call
    {
        return $this->callVerifierFactory->fromCall($this->spy->callAt($index));
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
     * Get the event count.
     *
     * @return int The event count.
     */
    public function count(): int
    {
        return $this->spy->count();
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
        return $this->spy->invokeWith($arguments);
    }

    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invoke(...$arguments)
    {
        return $this->spy->invokeWith($arguments);
    }

    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function __invoke(...$arguments)
    {
        return $this->spy->invokeWith($arguments);
    }

    /**
     * Checks if called.
     *
     * @return ?EventCollection The result.
     */
    public function checkCalled(): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $callCount = count($calls);

        if ($cardinality->matches($callCount, $callCount)) {
            return $this->assertionRecorder->createSuccess($calls);
        }

        return null;
    }

    /**
     * Throws an exception unless called.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function called(): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkCalled()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderCalled($this->spy, $cardinality)
        );
    }

    /**
     * Checks if called with the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return ?EventCollection The result.
     */
    public function checkCalledWith(...$arguments): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        $matchers = $this->matcherFactory->adaptAll($arguments);
        $calls = $this->spy->allCalls();
        $matchingEvents = [];
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier
                    ->matches($matchers, $call->arguments()->all())
            ) {
                $matchingEvents[] = $call;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless called with the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
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
                ->renderCalledWith($this->spy, $cardinality, $matchers)
        );
    }

    /**
     * Checks if this spy responded.
     *
     * @return ?EventCollection The result.
     */
    public function checkResponded(): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = [];
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if ($responseEvent = $call->responseEvent()) {
                $matchingEvents[] = $responseEvent;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless this spy responded.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function responded(): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkResponded()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderResponded($this->spy, $cardinality)
        );
    }

    /**
     * Checks if this spy completed.
     *
     * @return ?EventCollection The result.
     */
    public function checkCompleted(): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = [];
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if ($endEvent = $call->endEvent()) {
                $matchingEvents[] = $endEvent;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless this spy completed.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function completed(): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkCompleted()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderCompleted($this->spy, $cardinality)
        );
    }

    /**
     * Checks if this spy returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection The result.
     */
    public function checkReturned($value = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = [];
        $totalCount = count($calls);
        $matchCount = 0;

        if (0 === func_num_args()) {
            foreach ($calls as $call) {
                if (!$responseEvent = $call->responseEvent()) {
                    continue;
                }

                list($exception) = $call->response();

                if (!$exception) {
                    $matchingEvents[] = $responseEvent;
                    ++$matchCount;
                }
            }
        } else {
            $value = $this->matcherFactory->adapt($value);

            foreach ($calls as $call) {
                if (!$responseEvent = $call->responseEvent()) {
                    continue;
                }

                list($exception, $returnValue) = $call->response();

                if (!$exception && $value->matches($returnValue)) {
                    $matchingEvents[] = $responseEvent;
                    ++$matchCount;
                }
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless this spy returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
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
                ->renderReturned($this->spy, $cardinality, $value)
        );
    }

    /**
     * Checks if an exception of the supplied type was thrown.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection         The result.
     * @throws InvalidArgumentException If the type is invalid.
     */
    public function checkThrew($type = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = [];
        $totalCount = count($calls);
        $matchCount = 0;
        $isTypeSupported = false;

        if (!$type) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                if (!$responseEvent = $call->responseEvent()) {
                    continue;
                }

                list($exception) = $call->response();

                if ($exception) {
                    $matchingEvents[] = $responseEvent;
                    ++$matchCount;
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                if (!$responseEvent = $call->responseEvent()) {
                    continue;
                }

                list($exception) = $call->response();

                if ($exception && is_a($exception, $type)) {
                    $matchingEvents[] = $responseEvent;
                    ++$matchCount;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof InstanceHandle) {
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
                /** @var Matcher */
                $typeMatcher = $type;

                foreach ($calls as $call) {
                    if (!$responseEvent = $call->responseEvent()) {
                        continue;
                    }

                    list($exception) = $call->response();

                    if ($exception && $typeMatcher->matches($exception)) {
                        $matchingEvents[] = $responseEvent;
                        ++$matchCount;
                    }
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

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless an exception of the supplied type was thrown.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection         The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidArgumentException If the type is invalid.
     * @throws Throwable                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($type instanceof InstanceHandle) {
            /** @var Throwable */
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
                ->renderThrew($this->spy, $cardinality, $type)
        );
    }

    /**
     * Checks if this spy returned a generator.
     *
     * @return ?GeneratorVerifier The result.
     */
    public function checkGenerated(): ?GeneratorVerifier
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = [];
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if (!$call->responseEvent()) {
                continue;
            }

            list(, $returnValue) = $call->response();

            if ($returnValue instanceof Generator) {
                $matchingEvents[] = $call;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            /** @var GeneratorVerifier */
            $verifier = $this->assertionRecorder->createSuccessFromEventCollection(
                $this->generatorVerifierFactory
                    ->create($this->spy, $matchingEvents)
            );

            return $verifier;
        }

        return null;
    }

    /**
     * Throws an exception unless this spy returned a generator.
     *
     * @return GeneratorVerifier The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable         If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function generated(): ?GeneratorVerifier
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkGenerated()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderGenerated($this->spy, $cardinality)
        );
    }

    /**
     * Checks if this spy returned an iterable.
     *
     * @return ?IterableVerifier The result.
     */
    public function checkIterated(): ?IterableVerifier
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = [];
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if (!$call->responseEvent()) {
                continue;
            }

            list(, $returnValue) = $call->response();

            if ($returnValue instanceof Traversable || is_array($returnValue)) {
                $matchingEvents[] = $call;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            /** @var IterableVerifier */
            $verifier = $this->assertionRecorder->createSuccessFromEventCollection(
                $this->iterableVerifierFactory
                    ->create($this->spy, $matchingEvents)
            );

            return $verifier;
        }

        return null;
    }

    /**
     * Throws an exception unless this spy returned an iterable.
     *
     * @return IterableVerifier The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function iterated(): ?IterableVerifier
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkIterated()) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderIterated($this->spy, $cardinality)
        );
    }

    /**
     * Limits the output displayed when `var_dump` is used.
     *
     * @return array<string,mixed> The contents to export.
     */
    public function __debugInfo(): array
    {
        return ['spy' => $this->spy];
    }

    /**
     * @var Spy
     */
    private $spy;

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
     * @var CallVerifierFactory
     */
    private $callVerifierFactory;

    /**
     * @var AssertionRecorder
     */
    private $assertionRecorder;

    /**
     * @var AssertionRenderer
     */
    private $assertionRenderer;
}
