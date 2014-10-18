<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Call\Event\ProducedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedExceptionEventInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Cardinality\Verification\AbstractCardinalityVerifier;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Spy\Exception\UndefinedCallException;
use Exception;
use InvalidArgumentException;

/**
 * Provides convenience methods for verifying interactions with a spy.
 *
 * @internal
 */
class SpyVerifier extends AbstractCardinalityVerifier implements
    SpyVerifierInterface
{
    /**
     * Construct a new spy verifier.
     *
     * @param SpyInterface|null                 $spy                 The spy.
     * @param MatcherFactoryInterface|null      $matcherFactory      The matcher factory to use.
     * @param MatcherVerifierInterface|null     $matcherVerifier     The macther verifier to use.
     * @param CallVerifierFactoryInterface|null $callVerifierFactory The call verifier factory to use.
     * @param AssertionRecorderInterface|null   $assertionRecorder   The assertion recorder to use.
     * @param AssertionRendererInterface|null   $assertionRenderer   The assertion renderer to use.
     * @param InvocableInspectorInterface|null  $invocableInspector  The invocable inspector to use.
     */
    public function __construct(
        SpyInterface $spy = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        CallVerifierFactoryInterface $callVerifierFactory = null,
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null,
        InvocableInspectorInterface $invocableInspector = null
    ) {
        if (null === $spy) {
            $spy = new Spy();
        }
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $callVerifierFactory) {
            $callVerifierFactory = CallVerifierFactory::instance();
        }
        if (null === $assertionRecorder) {
            $assertionRecorder = AssertionRecorder::instance();
        }
        if (null === $assertionRenderer) {
            $assertionRenderer = AssertionRenderer::instance();
        }
        if (null === $invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }

        parent::__construct();

        $this->spy = $spy;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Get the spy.
     *
     * @return SpyInterface The spy.
     */
    public function spy()
    {
        return $this->spy;
    }

    /**
     * Get the matcher factory.
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    public function matcherFactory()
    {
        return $this->matcherFactory;
    }

    /**
     * Get the matcher verifier.
     *
     * @return MatcherVerifierInterface The matcher verifier.
     */
    public function matcherVerifier()
    {
        return $this->matcherVerifier;
    }

    /**
     * Get the call verifier factory.
     *
     * @return CallVerifierFactoryInterface The call verifier factory.
     */
    public function callVerifierFactory()
    {
        return $this->callVerifierFactory;
    }

    /**
     * Get the assertion recorder.
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    public function assertionRecorder()
    {
        return $this->assertionRecorder;
    }

    /**
     * Get the assertion renderer.
     *
     * @return AssertionRendererInterface The assertion renderer.
     */
    public function assertionRenderer()
    {
        return $this->assertionRenderer;
    }

    /**
     * Get the invocable inspector.
     *
     * @return InvocableInspectorInterface The invocable inspector.
     */
    public function invocableInspector()
    {
        return $this->invocableInspector;
    }

    /**
     * Returns true if anonymous.
     *
     * @return boolean True if anonymous.
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
     * Turn on or off the use of traversable spies.
     *
     * @param boolean $useTraversableSpies True to use traversable spies.
     */
    public function setUseTraversableSpies($useTraversableSpies)
    {
        $this->spy->setUseTraversableSpies($useTraversableSpies);
    }

    /**
     * Returns true if this spy uses traversable spies.
     *
     * @return boolean True if this spy uses traversable spies.
     */
    public function useTraversableSpies()
    {
        return $this->spy->useTraversableSpies();
    }

    /**
     * Turn on or off the use of generator spies.
     *
     * @param boolean $useGeneratorSpies True to use generator spies.
     */
    public function setUseGeneratorSpies($useGeneratorSpies)
    {
        $this->spy->setUseGeneratorSpies($useGeneratorSpies);
    }

    /**
     * Returns true if this spy uses generator spies.
     *
     * @return boolean True if this spy uses generator spies.
     */
    public function useGeneratorSpies()
    {
        return $this->spy->useGeneratorSpies();
    }

    /**
     * Get the identifier.
     *
     * @return string|null The identifier.
     */
    public function id()
    {
        return $this->spy->id();
    }

    /**
     * Set the calls.
     *
     * @param array<CallInterface> $calls The calls.
     */
    public function setCalls(array $calls)
    {
        $this->spy->setCalls($calls);
    }

    /**
     * Add a call.
     *
     * @param CallInterface $call The call.
     */
    public function addCall(CallInterface $call)
    {
        $this->spy->addCall($call);
    }

    /**
     * Get the recorded calls.
     *
     * @return array<CallInterface> The recorded calls.
     */
    public function recordedCalls()
    {
        return $this->callVerifierFactory
            ->adaptAll($this->spy->recordedCalls());
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param array<integer,mixed>|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invokeWith(array $arguments = null)
    {
        return $this->spy->invokeWith($arguments);
    }

    /**
     * Invoke this object.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invoke()
    {
        return $this->spy->invokeWith(func_get_args());
    }

    /**
     * Invoke this object.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function __invoke()
    {
        return $this->spy->invokeWith(func_get_args());
    }

    /**
     * Get the number of calls.
     *
     * @return integer The number of calls.
     */
    public function callCount()
    {
        return count($this->spy->recordedCalls());
    }

    /**
     * Get the call at a specific index.
     *
     * @param integer $index The call index.
     *
     * @return CallVerifierInterface  The call.
     * @throws UndefinedCallException If there is no call at the index.
     */
    public function callAt($index)
    {
        $calls = $this->spy->recordedCalls();

        if (!isset($calls[$index])) {
            throw new UndefinedCallException($index);
        }

        return $this->callVerifierFactory->adapt($calls[$index]);
    }

    /**
     * Get the first call.
     *
     * @return CallVerifierInterface  The call.
     * @throws UndefinedCallException If there is no first call.
     */
    public function firstCall()
    {
        $calls = $this->spy->recordedCalls();

        if (!isset($calls[0])) {
            throw new UndefinedCallException(0);
        }

        return $this->callVerifierFactory->adapt($calls[0]);
    }

    /**
     * Get the last call.
     *
     * @return CallVerifierInterface  The call.
     * @throws UndefinedCallException If there is no last call.
     */
    public function lastCall()
    {
        $callCount = count($this->spy->recordedCalls());

        if ($callCount > 0) {
            $index = $callCount - 1;
        } else {
            $index = 0;
        }

        $calls = $this->spy->recordedCalls();

        if (!isset($calls[$index])) {
            throw new UndefinedCallException($index);
        }

        return $this->callVerifierFactory->adapt($calls[$index]);
    }

    /**
     * Checks if called.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkCalled()
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);

        if ($cardinality->matches($callCount, $callCount)) {
            return $this->assertionRecorder->createSuccess($calls);
        }
    }

    /**
     * Throws an exception unless called.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public function called()
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkCalled()) {
            return $result;
        }

        $calls = $this->spy->recordedCalls();
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

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $renderedCardinality,
                $renderedActual
            )
        );
    }

    /**
     * Checks if called with the supplied arguments (and possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkCalledWith()
    {
        $cardinality = $this->resetCardinality();

        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $calls = $this->spy->recordedCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                $matchingEvents[] = $call;
                $matchCount++;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
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

        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);

        if (0 === $callCount) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Calls:\n%s",
                $this->assertionRenderer->renderCallsArguments($calls)
            );
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Expected %s with arguments like:\n    %s\n%s",
                $this->assertionRenderer
                    ->renderCardinality($cardinality, 'call'),
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
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkCalledOn($value)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->recordedCalls();
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
                    $matchCount++;
                }
            }
        } else {
            foreach ($calls as $call) {
                $thisValue = $this->invocableInspector
                    ->callbackThisValue($call->callback());

                if ($thisValue === $value) {
                    $matchingEvents[] = $call;
                    $matchCount++;
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
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
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

        $calls = $this->spy->recordedCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Called on:\n%s",
                $this->assertionRenderer->renderThisValues($calls)
            );
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Expected %s. %s",
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
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkReturned($value = null)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->recordedCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        if (0 === func_num_args()) {
            foreach ($calls as $call) {
                $response = $call->responseEvent();

                if ($response && !$call->exception()) {
                    $matchingEvents[] = $response;
                    $matchCount++;
                }
            }
        } else {
            $value = $this->matcherFactory->adapt($value);

            foreach ($calls as $call) {
                $response = $call->responseEvent();

                if (
                    $response &&
                    !$call->exception() &&
                    $value->matches($call->returnValue())
                ) {
                    $matchingEvents[] = $response;
                    $matchCount++;
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
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
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

        if (0 === $argumentCount) {
            $renderedType = 'call to return';
        } else {
            $renderedType =
                sprintf('call to return like %s', $value->describe());
        }

        $calls = $this->spy->recordedCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls)
            );
        }

        throw $this->assertionRecorder->createFailure(
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
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface|null The result.
     * @throws InvalidArgumentException          If the type is invalid.
     */
    public function checkThrew($type = null)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->recordedCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;
        $isTypeSupported = false;

        if (null === $type) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                if ($call->exception()) {
                    $matchingEvents[] = $call->responseEvent();
                    $matchCount++;
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                if (is_a($call->exception(), $type)) {
                    $matchingEvents[] = $call->responseEvent();
                    $matchCount++;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($calls as $call) {
                    if ($call->exception() == $type) {
                        $matchingEvents[] = $call->responseEvent();
                        $matchCount++;
                    }
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);

                foreach ($calls as $call) {
                    $exception = $call->exception();

                    if ($exception && $type->matches($exception)) {
                        $matchingEvents[] = $call->responseEvent();
                        $matchCount++;
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
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface The result.
     * @throws InvalidArgumentException     If the type is invalid.
     * @throws Exception                    If the assertion fails.
     */
    public function threw($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkThrew($type)) {
            return $result;
        }

        if (null === $type) {
            $renderedType = 'call to throw';
        } elseif (is_string($type)) {
            $renderedType = sprintf(
                'call to throw %s exception',
                $this->assertionRenderer->renderValue($type)
            );
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                $renderedType = sprintf(
                    'call to throw exception equal to %s',
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'call to throw exception like %s',
                    $this->matcherFactory->adapt($type)->describe()
                );
            }
        }

        $calls = $this->spy->recordedCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls)
            );
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if this spy produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the spy
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
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkProduced($keyOrValue = null, $value = null)
    {
        $cardinality = $this->resetCardinality();

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $checkKey = false;
            $checkValue = false;
        } elseif (1 === $argumentCount) {
            $checkKey = false;
            $checkValue = true;
            $value = $this->matcherFactory->adapt($keyOrValue);
        } else {
            $checkKey = true;
            $checkValue = true;
            $key = $this->matcherFactory->adapt($keyOrValue);
            $value = $this->matcherFactory->adapt($value);
        }

        $calls = $this->spy->recordedCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            foreach ($call->traversableEvents() as $event) {
                if ($event instanceof ProducedEventInterface) {
                    if ($checkKey && !$key->matches($event->key())) {
                        continue;
                    }
                    if ($checkValue && !$value->matches($event->value())) {
                        continue;
                    }

                    $matchingEvents[] = $event;
                    $matchCount++;

                    break;
                }
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this spy produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the spy
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
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public function produced($keyOrValue = null, $value = null)
    {
        $cardinality = $this->cardinality;

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = array();
        } elseif (1 === $argumentCount) {
            $value = $this->matcherFactory->adapt($keyOrValue);
            $arguments = array($value);
        } else {
            $key = $this->matcherFactory->adapt($keyOrValue);
            $value = $this->matcherFactory->adapt($value);
            $arguments = array($key, $value);
        }

        if (
            $result =
                call_user_func_array(array($this, 'checkProduced'), $arguments)
        ) {
            return $result;
        }

        if (0 === $argumentCount) {
            $renderedType = 'call to produce';
        } elseif (1 === $argumentCount) {
            $renderedType =
                sprintf('call to produce like %s', $value->describe());
        } else {
            $renderedType = sprintf(
                'call to produce like %s => %s',
                $key->describe(),
                $value->describe()
            );
        }

        $calls = $this->spy->recordedCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls, true)
            );
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if this spy produced all of the supplied key-value pairs, in the
     * supplied order.
     *
     * @param mixed $pairs,... The key-value pairs.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkProducedAll()
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->recordedCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;
        $pairs = array();
        $pairCount = func_num_args();

        foreach (func_get_args() as $pair) {
            if (is_array($pair)) {
                $pairs[] = array(
                    $this->matcherFactory->adapt($pair[0]),
                    $this->matcherFactory->adapt($pair[1]),
                );
            } else {
                $pairs[] = $this->matcherFactory->adapt($pair);
            }
        }

        foreach ($calls as $call) {
            $producedEvents = array();
            $lastEvent = $call->responseEvent();

            foreach ($call->traversableEvents() as $event) {
                if ($event instanceof ProducedEventInterface) {
                    $producedEvents[] = $event;
                    $lastEvent = $event;
                }
            }

            if (count($producedEvents) === $pairCount) {
                $isMatch = true;

                foreach ($pairs as $index => $pair) {
                    if (is_array($pair)) {
                        if (
                            !$pair[0]->matches($producedEvents[$index]->key())
                        ) {
                            $isMatch = false;

                            break;
                        }

                        $value = $pair[1];
                    } else {
                        $value = $pair;
                    }

                    if (!$value->matches($producedEvents[$index]->value())) {
                        $isMatch = false;

                        break;
                    }
                }

                if ($isMatch) {
                    $matchingEvents[] = $lastEvent;
                    $matchCount++;
                }
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this spy produced all of the supplied
     * key-value pairs, in the supplied order.
     *
     * @param mixed $pairs,... The key-value pairs.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public function producedAll()
    {
        $cardinality = $this->cardinality;

        $pairs = array();

        foreach (func_get_args() as $pair) {
            if (is_array($pair)) {
                $pairs[] = array(
                    $this->matcherFactory->adapt($pair[0]),
                    $this->matcherFactory->adapt($pair[1]),
                );
            } else {
                $pairs[] = $this->matcherFactory->adapt($pair);
            }
        }

        if (
            $result =
                call_user_func_array(array($this, 'checkProducedAll'), $pairs)
        ) {
            return $result;
        }

        if (0 === func_num_args()) {
            $renderedType = 'call to produce nothing. ';
        } else {
            $renderedType = 'call to produce like:';

            foreach ($pairs as $pair) {
                if (is_array($pair)) {
                    $renderedType .= sprintf(
                        "\n    - %s => %s",
                        $pair[0]->describe(),
                        $pair[1]->describe()
                    );
                } else {
                    $renderedType .= sprintf("\n    - %s", $pair->describe());
                }
            }

            $renderedType .= "\n";
        }

        $calls = $this->spy->recordedCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls, true)
            );
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s%s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if this spy received the supplied value.
     *
     * When called with no arguments, this method simply checks that the spy
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkReceived($value = null)
    {
        $cardinality = $this->resetCardinality();

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $checkValue = false;
        } else {
            $checkValue = true;
            $value = $this->matcherFactory->adapt($value);
        }

        $calls = $this->spy->recordedCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            foreach ($call->traversableEvents() as $event) {
                if ($event instanceof ReceivedEventInterface) {
                    if (!$checkValue || $value->matches($event->value())) {
                        $matchingEvents[] = $event;
                        $matchCount++;

                        break;
                    }
                }
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this spy received the supplied value.
     *
     * When called with no arguments, this method simply checks that the spy
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public function received($value = null)
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
                call_user_func_array(array($this, 'checkReceived'), $arguments)
        ) {
            return $result;
        }

        if (0 === $argumentCount) {
            $renderedType = 'generator to receive value';
        } else {
            $renderedType = sprintf(
                'generator to receive value like %s',
                $value->describe()
            );
        }

        $calls = $this->spy->recordedCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls, true)
            );
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if this spy received an exception of the supplied type.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface|null The result.
     * @throws InvalidArgumentException          If the type is invalid.
     */
    public function checkReceivedException($type = null)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->recordedCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;
        $isTypeSupported = false;

        if (null === $type) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                foreach ($call->traversableEvents() as $event) {
                    if ($event instanceof ReceivedExceptionEventInterface) {
                        $matchingEvents[] = $event;
                        $matchCount++;

                        break;
                    }
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                foreach ($call->traversableEvents() as $event) {
                    if ($event instanceof ReceivedExceptionEventInterface) {
                        if (is_a($event->exception(), $type)) {
                            $matchingEvents[] = $event;
                            $matchCount++;

                            break;
                        }
                    }
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($calls as $call) {
                    foreach ($call->traversableEvents() as $event) {
                        if ($event instanceof ReceivedExceptionEventInterface) {
                            if ($event->exception() == $type) {
                                $matchingEvents[] = $event;
                                $matchCount++;

                                break;
                            }
                        }
                    }
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);

                foreach ($calls as $call) {
                    foreach ($call->traversableEvents() as $event) {
                        if ($event instanceof ReceivedExceptionEventInterface) {
                            if ($type->matches($event->exception())) {
                                $matchingEvents[] = $event;
                                $matchCount++;

                                break;
                            }
                        }
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
     * Throws an exception unless this spy received an exception of the
     * supplied type.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface The result.
     * @throws InvalidArgumentException     If the type is invalid.
     * @throws Exception                    If the assertion fails.
     */
    public function receivedException($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkReceivedException($type)) {
            return $result;
        }

        if (null === $type) {
            $renderedType = 'generator to receive exception';
        } elseif (is_string($type)) {
            $renderedType = sprintf(
                'generator to receive %s exception',
                $this->assertionRenderer->renderValue($type)
            );
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                $renderedType = sprintf(
                    'generator to receive exception equal to %s',
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'generator to receive exception like %s',
                    $this->matcherFactory->adapt($type)->describe()
                );
            }
        }

        $calls = $this->spy->recordedCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls, true)
            );
        }

        throw $this->assertionRecorder->createFailure(
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
    private $callVerifierFactory;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
}
