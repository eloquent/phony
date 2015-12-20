<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Call\Event\ProducedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedExceptionEventInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Cardinality\Verification\AbstractCardinalityVerifier;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Error;
use Exception;
use InvalidArgumentException;
use Iterator;
use Throwable;

/**
 * Provides convenience methods for verifying interactions with a spy.
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
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return $this->spy->hasEvents();
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return boolean True if this collection contains any calls.
     */
    public function hasCalls()
    {
        return $this->spy->hasCalls();
    }

    /**
     * Get the number of events.
     *
     * @return integer The event count.
     */
    public function eventCount()
    {
        return $this->spy->eventCount();
    }

    /**
     * Get the number of calls.
     *
     * @return integer The call count.
     */
    public function callCount()
    {
        return $this->spy->callCount();
    }

    /**
     * Get all events as an array.
     *
     * @return array<EventInterface> The events.
     */
    public function allEvents()
    {
        return $this->spy->allEvents();
    }

    /**
     * Get all calls as an array.
     *
     * @return array<CallVerifierInterface> The calls.
     */
    public function allCalls()
    {
        return $this->callVerifierFactory->adaptAll($this->spy->allCalls());
    }

    /**
     * Get an event by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt($index = 0)
    {
        return $this->spy->eventAt($index);
    }

    /**
     * Get the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
        return $this->callVerifierFactory->adapt($this->spy->firstCall());
    }

    /**
     * Get the last call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall()
    {
        return $this->callVerifierFactory->adapt($this->spy->lastCall());
    }

    /**
     * Get a call by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return CallVerifierInterface  The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt($index = 0)
    {
        return $this->callVerifierFactory->adapt($this->spy->callAt($index));
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
     * @return integer The event count.
     */
    public function count()
    {
        return $this->spy->count();
    }

    /**
     * Get the arguments.
     *
     * @return ArgumentsInterface|null The arguments.
     * @throws UndefinedCallException  If there are no calls.
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
     * @param integer $index The index.
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
     * @param ArgumentsInterface|array $arguments The arguments.
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
     * @return EventCollectionInterface|null The result.
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
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
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
     * @return EventCollectionInterface|null The result.
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
                $this->matcherVerifier->matches($matchers, $call->arguments())
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
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
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
     * @return EventCollectionInterface|null The result.
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
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
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
     * @return EventCollectionInterface|null The result.
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
                $response = $call->responseEvent();

                if ($response && !$call->exception()) {
                    $matchingEvents[] = $response;
                    ++$matchCount;
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
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
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
     * @return EventCollectionInterface|null The result.
     * @throws InvalidArgumentException      If the type is invalid.
     */
    public function checkThrew($type = null)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;
        $isTypeSupported = false;

        if (null === $type) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                if ($call->exception()) {
                    $matchingEvents[] = $call->responseEvent();
                    ++$matchCount;
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($calls as $call) {
                if (is_a($call->exception(), $type)) {
                    $matchingEvents[] = $call->responseEvent();
                    ++$matchCount;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($calls as $call) {
                    if ($call->exception() == $type) {
                        $matchingEvents[] = $call->responseEvent();
                        ++$matchCount;
                    }
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);

                foreach ($calls as $call) {
                    $exception = $call->exception();

                    if ($exception && $type->matches($exception)) {
                        $matchingEvents[] = $call->responseEvent();
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
     * @return EventCollectionInterface The result.
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

        if (null === $type) {
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
     * @return EventCollectionInterface|null The result.
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

        $calls = $this->spy->allCalls();
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
                    ++$matchCount;
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
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
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

        $renderedSubject = $this->assertionRenderer->renderCallable($this->spy);

        if (0 === $argumentCount) {
            $renderedType = sprintf('call on %s to produce', $renderedSubject);
        } elseif (1 === $argumentCount) {
            $renderedType = sprintf(
                'call on %s to produce like %s',
                $renderedSubject,
                $value->describe()
            );
        } else {
            $renderedType = sprintf(
                'call on %s to produce like %s: %s',
                $renderedSubject,
                $key->describe(),
                $value->describe()
            );
        }

        $calls = $this->spy->allCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls, true)
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
     * Checks if this spy produced all of the supplied key-value pairs, in the
     * supplied order.
     *
     * @param mixed ...$pairs The key-value pairs.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkProducedAll()
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
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
                    ++$matchCount;
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
     * @param mixed ...$pairs The key-value pairs.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
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

        $renderedSubject = $this->assertionRenderer->renderCallable($this->spy);

        if (0 === func_num_args()) {
            $renderedType =
                sprintf('call on %s to produce nothing. ', $renderedSubject);
        } else {
            $renderedType =
                sprintf('call on %s to produce like:', $renderedSubject);

            foreach ($pairs as $pair) {
                if (is_array($pair)) {
                    $renderedType .= sprintf(
                        "\n    - %s: %s",
                        $pair[0]->describe(),
                        $pair[1]->describe()
                    );
                } else {
                    $renderedType .= sprintf("\n    - %s", $pair->describe());
                }
            }

            $renderedType .= "\n";
        }

        $calls = $this->spy->allCalls();

        if (0 === count($calls)) {
            $renderedActual = 'Never called.';
        } else {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($calls, true)
            );
        }

        return $this->assertionRecorder->createFailure(
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
     * @return EventCollectionInterface|null The result.
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

        $calls = $this->spy->allCalls();
        $matchingEvents = array();
        $totalCount = count($calls);
        $matchCount = 0;

        foreach ($calls as $call) {
            foreach ($call->traversableEvents() as $event) {
                if ($event instanceof ReceivedEventInterface) {
                    if (!$checkValue || $value->matches($event->value())) {
                        $matchingEvents[] = $event;
                        ++$matchCount;
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
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
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

        $renderedSubject = $this->assertionRenderer->renderCallable($this->spy);

        if (0 === $argumentCount) {
            $renderedType = sprintf(
                'generator returned by %s to receive value',
                $renderedSubject
            );
        } else {
            $renderedType = sprintf(
                'generator returned by %s to receive value like %s',
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
                $this->assertionRenderer->renderResponses($calls, true)
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
     * Checks if this spy received an exception of the supplied type.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface|null The result.
     * @throws InvalidArgumentException      If the type is invalid.
     */
    public function checkReceivedException($type = null)
    {
        $cardinality = $this->resetCardinality();

        $calls = $this->spy->allCalls();
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
                        ++$matchCount;
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
                            ++$matchCount;
                        }
                    }
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($calls as $call) {
                    foreach ($call->traversableEvents() as $event) {
                        if ($event instanceof ReceivedExceptionEventInterface) {
                            if ($event->exception() == $type) {
                                $matchingEvents[] = $event;
                                ++$matchCount;
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
                                ++$matchCount;
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
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface The result.
     * @throws InvalidArgumentException If the type is invalid.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function receivedException($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkReceivedException($type)) {
            return $result;
        }

        $renderedSubject = $this->assertionRenderer->renderCallable($this->spy);

        if (null === $type) {
            $renderedType = sprintf(
                'generator returned by %s to receive exception',
                $renderedSubject
            );
        } elseif (is_string($type)) {
            $renderedType = sprintf(
                'generator returned by %s to receive %s exception',
                $renderedSubject,
                $type
            );
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $renderedType = sprintf(
                    'generator returned by %s to receive exception equal to %s',
                    $renderedSubject,
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'generator returned by %s to receive exception like %s',
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
                $this->assertionRenderer->renderResponses($calls, true)
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
    private $callVerifierFactory;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
}
