<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallVerifier;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Cardinality\AbstractCardinalityVerifier;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Verification\GeneratorVerifier;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\TraversableVerifier;
use Eloquent\Phony\Verification\TraversableVerifierFactory;
use Error;
use Exception;
use Generator;
use InvalidArgumentException;
use Iterator;
use Throwable;
use Traversable;

/**
 * Provides convenience methods for verifying interactions with a spy.
 */
class SpyVerifier extends AbstractCardinalityVerifier implements Spy
{
    /**
     * Construct a new spy verifier.
     *
     * @param Spy                        $spy                        The spy.
     * @param MatcherFactory             $matcherFactory             The matcher factory to use.
     * @param MatcherVerifier            $matcherVerifier            The macther verifier to use.
     * @param GeneratorVerifierFactory   $generatorVerifierFactory   The generator verifier factory to use.
     * @param TraversableVerifierFactory $traversableVerifierFactory The traversable verifier factory to use.
     * @param CallVerifierFactory        $callVerifierFactory        The call verifier factory to use.
     * @param AssertionRecorder          $assertionRecorder          The assertion recorder to use.
     * @param AssertionRenderer          $assertionRenderer          The assertion renderer to use.
     * @param InvocableInspector         $invocableInspector         The invocable inspector to use.
     */
    public function __construct(
        Spy $spy,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        TraversableVerifierFactory $traversableVerifierFactory,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        InvocableInspector $invocableInspector
    ) {
        parent::__construct();

        $this->spy = $spy;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->generatorVerifierFactory = $generatorVerifierFactory;
        $this->traversableVerifierFactory = $traversableVerifierFactory;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Get the spy.
     *
     * @return Spy The spy.
     */
    public function spy()
    {
        return $this->spy;
    }

    /**
     * Returns true if anonymous.
     *
     * @return bool True if anonymous.
     */
    public function isAnonymous()
    {
        return $this->spy->isAnonymous();
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback()
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
    public function setUseGeneratorSpies($useGeneratorSpies)
    {
        $this->spy->setUseGeneratorSpies($useGeneratorSpies);

        return $this;
    }

    /**
     * Returns true if this spy uses generator spies.
     *
     * @return bool True if this spy uses generator spies.
     */
    public function useGeneratorSpies()
    {
        return $this->spy->useGeneratorSpies();
    }

    /**
     * Turn on or off the use of traversable spies.
     *
     * @param bool $useTraversableSpies True to use traversable spies.
     *
     * @return $this This spy.
     */
    public function setUseTraversableSpies($useTraversableSpies)
    {
        $this->spy->setUseTraversableSpies($useTraversableSpies);

        return $this;
    }

    /**
     * Returns true if this spy uses traversable spies.
     *
     * @return bool True if this spy uses traversable spies.
     */
    public function useTraversableSpies()
    {
        return $this->spy->useTraversableSpies();
    }

    /**
     * Set the label.
     *
     * @param string|null $label The label.
     *
     * @return $this This invocable.
     */
    public function setLabel($label)
    {
        $this->spy->setLabel($label);

        return $this;
    }

    /**
     * Get the label.
     *
     * @return string|null The label.
     */
    public function label()
    {
        return $this->spy->label();
    }

    /**
     * Stop recording calls.
     *
     * @return $this This spy.
     */
    public function stopRecording()
    {
        $this->spy->stopRecording();

        return $this;
    }

    /**
     * Start recording calls.
     *
     * @return $this This spy.
     */
    public function startRecording()
    {
        $this->spy->startRecording();

        return $this;
    }

    /**
     * Set the calls.
     *
     * @param array<Call> $calls The calls.
     */
    public function setCalls(array $calls)
    {
        $this->spy->setCalls($calls);
    }

    /**
     * Add a call.
     *
     * @param Call $call The call.
     */
    public function addCall(Call $call)
    {
        $this->spy->addCall($call);
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return bool True if this collection contains any events.
     */
    public function hasEvents()
    {
        return $this->spy->hasEvents();
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return bool True if this collection contains any calls.
     */
    public function hasCalls()
    {
        return $this->spy->hasCalls();
    }

    /**
     * Get the number of events.
     *
     * @return int The event count.
     */
    public function eventCount()
    {
        return $this->spy->eventCount();
    }

    /**
     * Get the number of calls.
     *
     * @return int The call count.
     */
    public function callCount()
    {
        return $this->spy->callCount();
    }

    /**
     * Get all events as an array.
     *
     * @return array<Event> The events.
     */
    public function allEvents()
    {
        return $this->spy->allEvents();
    }

    /**
     * Get all calls as an array.
     *
     * @return array<CallVerifier> The calls.
     */
    public function allCalls()
    {
        return $this->callVerifierFactory->fromCalls($this->spy->allCalls());
    }

    /**
     * Get the first event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent()
    {
        return $this->spy->firstEvent();
    }

    /**
     * Get the last event.
     *
     * @return Event                   The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent()
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
    public function eventAt($index = 0)
    {
        return $this->spy->eventAt($index);
    }

    /**
     * Get the first call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
        return $this->callVerifierFactory->fromCall($this->spy->firstCall());
    }

    /**
     * Get the last call.
     *
     * @return Call                   The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall()
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
    public function callAt($index = 0)
    {
        return $this->callVerifierFactory->fromCall($this->spy->callAt($index));
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->allCalls());
    }

    /**
     * Get the event count.
     *
     * @return int The event count.
     */
    public function count()
    {
        return $this->spy->count();
    }

    /**
     * Get the arguments.
     *
     * @return Arguments|null         The arguments.
     * @throws UndefinedCallException If there are no calls.
     */
    public function arguments()
    {
        return $this->spy->arguments();
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
    public function argument($index = 0)
    {
        return $this->spy->argument($index);
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array $arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invokeWith($arguments = array())
    {
        return $this->spy->invokeWith($arguments);
    }

    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invoke()
    {
        return $this->spy->invokeWith(func_get_args());
    }

    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function __invoke()
    {
        return $this->spy->invokeWith(func_get_args());
    }

    /**
     * Checks if called.
     *
     * @return EventCollection|null The result.
     */
    public function checkCalled()
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $callCount = count($calls);

        if ($cardinality->matches($callCount, $callCount)) {
            return $this->assertionRecorder->createSuccess($calls);
        }
    }

    /**
     * Throws an exception unless called.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function called()
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkCalled()) {
            return $result;
        }

        $calls = $this->spy->allCalls();
        $renderedCardinality = $this->assertionRenderer
            ->renderCardinality($cardinality, 'call');

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Calls:\n%s",
                $this->assertionRenderer->renderCalls($calls)
            );
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $renderedCardinality,
                $renderedActual
            )
        );
    }

    /**
     * Checks if called with the supplied arguments.
     *
     * @param mixed ...$argument The arguments.
     *
     * @return EventCollection|null The result.
     */
    public function checkCalledWith()
    {
        $cardinality = $this->resetCardinality();

        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $calls = $this->spy->allCalls();
        $matchingEvents = array();
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
    }

    /**
     * Throws an exception unless called with the supplied arguments.
     *
     * @param mixed ...$argument The arguments.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledWith()
    {
        $cardinality = $this->cardinality;

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        if (
            $result =
                call_user_func_array(array($this, 'checkCalledWith'), $matchers)
        ) {
            return $result;
        }

        $calls = $this->spy->allCalls();
        $callCount = count($calls);

        if (0 === $callCount) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Calls:\n%s",
                $this->assertionRenderer->renderCallsArguments($calls)
            );
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                "Expected %s with arguments like:\n    %s\n%s",
                $this->assertionRenderer->renderCardinality(
                    $cardinality,
                    'call on ' .
                        $this->assertionRenderer->renderCallable($this->spy)
                ),
                $this->assertionRenderer->renderMatchers($matchers),
                $renderedActual
            )
        );
    }

    /**
     * Checks if the $this value is the same as the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollection|null The result.
     */
    public function checkCalledOn($value)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);

            foreach ($calls as $call) {
                $thisValue = $this->invocableInspector
                    ->callbackThisValue($call->callback());

                if ($value->matches($thisValue)) {
                    $matchingEvents[] = $call;
                    ++$matchCount;
                }
            }
        } else {
            foreach ($calls as $call) {
                $thisValue = $this->invocableInspector
                    ->callbackThisValue($call->callback());

                if ($thisValue === $value) {
                    $matchingEvents[] = $call;
                    ++$matchCount;
                }
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless the $this value is the same as the supplied
     * value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledOn($value)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkCalledOn($value)) {
            return $result;
        }

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);
            $renderedType =
                sprintf('call on object like %s', $value->describe());
        } else {
            $renderedType = 'call on supplied object';
        }

        $calls = $this->spy->allCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Called on:\n%s",
                $this->assertionRenderer->renderThisValues($calls)
            );
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if this spy returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollection|null The result.
     */
    public function checkReturned($value = null)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        if (0 === func_num_args()) {
            foreach ($calls as $call) {
                if (!$responseEvent = $call->responseEvent()) {
                    continue;
                }

                list($exception, $returnValue) = $call->response();

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
    }

    /**
     * Throws an exception unless this spy returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function returned($value = null)
    {
        $cardinality = $this->cardinality;

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = array();
        } else {
            $value = $this->matcherFactory->adapt($value);
            $arguments = array($value);
        }

        if (
            $result =
                call_user_func_array(array($this, 'checkReturned'), $arguments)
        ) {
            return $result;
        }

        $renderedSubject = $this->assertionRenderer->renderCallable($this->spy);

        if (0 === $argumentCount) {
            $renderedType = sprintf('call on %s to return', $renderedSubject);
        } else {
            $renderedType = sprintf(
                'call on %s to return like %s',
                $renderedSubject,
                $value->describe()
            );
        }

        $calls = $this->spy->allCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls)
            );
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if an exception of the supplied type was thrown.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If the type is invalid.
     */
    public function checkThrew($type = null)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;
        $isTypeSupported = false;

        if (!$type) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                if (!$responseEvent = $call->responseEvent()) {
                    continue;
                }

                list($exception, $returnValue) = $call->response();

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

                list($exception, $returnValue) = $call->response();

                if ($exception && is_a($exception, $type)) {
                    $matchingEvents[] = $responseEvent;
                    ++$matchCount;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($calls as $call) {
                    if (!$responseEvent = $call->responseEvent()) {
                        continue;
                    }

                    list($exception, $returnValue) = $call->response();

                    if ($exception == $type) {
                        $matchingEvents[] = $responseEvent;
                        ++$matchCount;
                    }
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);

                foreach ($calls as $call) {
                    if (!$responseEvent = $call->responseEvent()) {
                        continue;
                    }

                    list($exception, $returnValue) = $call->response();

                    if ($exception && $type->matches($exception)) {
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
    }

    /**
     * Throws an exception unless an exception of the supplied type was thrown.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If the type is invalid.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkThrew($type)) {
            return $result;
        }

        $renderedSubject = $this->assertionRenderer->renderCallable($this->spy);

        if (!$type) {
            $renderedType = sprintf('call on %s to throw', $renderedSubject);
        } elseif (is_string($type)) {
            $renderedType = sprintf(
                'call on %s to throw %s exception',
                $renderedSubject,
                $type
            );
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $renderedType = sprintf(
                    'call on %s to throw exception equal to %s',
                    $renderedSubject,
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'call on %s to throw exception like %s',
                    $renderedSubject,
                    $this->matcherFactory->adapt($type)->describe()
                );
            }
        }

        $calls = $this->spy->allCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls)
            );
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if this spy returned a generator.
     *
     * @return GeneratorVerifier|null The result.
     */
    public function checkGenerated()
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if (!$responseEvent = $call->responseEvent()) {
                continue;
            }

            list($exception, $returnValue) = $call->response();

            if ($returnValue instanceof Generator) {
                $matchingEvents[] = $call;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccessFromEventCollection(
                $this->generatorVerifierFactory->create($this, $matchingEvents)
            );
        }
    }

    /**
     * Throws an exception unless this spy returned a generator.
     *
     * @return GeneratorVerifier The result.
     * @throws Exception         If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function generated()
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkGenerated()) {
            return $result;
        }

        $renderedType = sprintf(
            'call on %s to generate',
            $this->assertionRenderer->renderCallable($this->spy)
        );

        $calls = $this->spy->allCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls)
            );
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if this spy returned a traversable.
     *
     * @return TraversableVerifier|null The result.
     */
    public function checkTraversed()
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if (!$responseEvent = $call->responseEvent()) {
                continue;
            }

            list($exception, $returnValue) = $call->response();

            if ($returnValue instanceof Traversable || is_array($returnValue)) {
                $matchingEvents[] = $call;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccessFromEventCollection(
                $this->traversableVerifierFactory
                    ->create($this, $matchingEvents)
            );
        }
    }

    /**
     * Throws an exception unless this spy returned a traversable.
     *
     * @return TraversableVerifier The result.
     * @throws Exception           If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function traversed()
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkTraversed()) {
            return $result;
        }

        $renderedType = sprintf(
            'call on %s to be traversable',
            $this->assertionRenderer->renderCallable($this->spy)
        );

        $calls = $this->spy->allCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls)
            );
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    private $spy;
    private $matcherFactory;
    private $matcherVerifier;
    private $generatorVerifierFactory;
    private $traversableVerifierFactory;
    private $callVerifierFactory;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
}
