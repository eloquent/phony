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
use Eloquent\Phony\Assertion\Result\AssertionResultInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Spy\Exception\UndefinedCallException;
use Exception;

/**
 * Provides convenience methods for verifying interactions with a spy.
 *
 * @internal
 */
class SpyVerifier implements SpyVerifierInterface
{
    /**
     * Merge all calls made on the supplied spies, and sort them by sequence.
     *
     * @param array<SpyInterface> $spies The spies.
     *
     * @return array<integer,CallInterface> The calls.
     */
    public static function mergeCalls(array $spies)
    {
        $calls = array();

        foreach ($spies as $spy) {
            foreach ($spy->recordedCalls() as $call) {
                if (!in_array($call, $calls, true)) {
                    $calls[] = $call;
                }
            }
        }

        usort($calls, get_class() . '::compareCallOrder');

        return $calls;
    }

    /**
     * Compare the supplied calls by call order.
     *
     * Returns typical comparator values, similar to strcmp().
     *
     * @see strcmp()
     *
     * @param CallInterface $left  The left call.
     * @param CallInterface $right The right call.
     *
     * @return integer The comparison result.
     */
    public static function compareCallOrder(
        CallInterface $left,
        CallInterface $right
    ) {
        return $left->sequenceNumber() - $right->sequenceNumber();
    }

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
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback()
    {
        return $this->spy->callback();
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
     * Returns true if called at least once.
     *
     * @return boolean True if called at least once.
     */
    public function called()
    {
        return count($this->spy->recordedCalls()) > 0;
    }

    /**
     * Throws an exception unless called at least once.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalled()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure('Never called.');
        }

        return $this->assertionRecorder->createSuccess($calls);
    }

    /**
     * Returns true if called only once.
     *
     * @return boolean True if called only once.
     */
    public function calledOnce()
    {
        return 1 === count($this->spy->recordedCalls());
    }

    /**
     * Throws an exception unless called only once.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledOnce()
    {
        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);

        if (1 !== $callCount) {
            throw $this->assertionRecorder->createFailure(
                sprintf('Expected 1 call. Called %d time(s).', $callCount)
            );
        }

        return $this->assertionRecorder->createSuccess($calls);
    }

    /**
     * Returns true if called an exact amount of times.
     *
     * @param integer $times The expected number of calls.
     *
     * @return boolean True if called an exact amount of times.
     */
    public function calledTimes($times)
    {
        return $times === count($this->spy->recordedCalls());
    }

    /**
     * Throws an exception unless called an exact amount of times.
     *
     * @param integer $times The expected number of calls.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledTimes($times)
    {
        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);

        if ($times !== $callCount) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected %d call(s). Called %d time(s).',
                    $times,
                    $callCount
                )
            );
        }

        return $this->assertionRecorder->createSuccess($calls);
    }

    /**
     * Returns true if this spy was called before the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return boolean True if this spy was called before the supplied spy.
     */
    public function calledBefore(SpyInterface $spy)
    {
        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);
        if ($callCount < 1) {
            return false;
        }

        $otherCalls = $spy->recordedCalls();
        $otherCallCount = count($otherCalls);
        if ($otherCallCount < 1) {
            return false;
        }

        $firstCall = $calls[0];
        $otherLastCall = $otherCalls[$otherCallCount - 1];

        return $firstCall->sequenceNumber() < $otherLastCall->sequenceNumber();
    }

    /**
     * Throws an exception unless this spy was called before the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledBefore(SpyInterface $spy)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            throw $this->assertionRecorder
                ->createFailure("Not called before supplied spy. Never called.");
        }

        $otherCalls = $spy->recordedCalls();
        $otherCallCount = count($otherCalls);

        if ($otherCallCount < 1) {
            throw $this->assertionRecorder->createFailure(
                "Not called before supplied spy. Supplied spy never called."
            );
        }

        $matchingCalls = array();

        if ($otherCallCount > 0) {
            $lastCall = $otherCalls[$otherCallCount - 1];

            foreach ($calls as $call) {
                if ($call->sequenceNumber() < $lastCall->sequenceNumber()) {
                    $matchingCalls[] = $call;
                }
            }
        }

        if ($matchingCalls) {
            return $this->assertionRecorder->createSuccess($matchingCalls);
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Not called before supplied spy. Actual calls:\n%s",
                $this->assertionRenderer
                    ->renderCalls(static::mergeCalls(array($this->spy, $spy)))
            )
        );
    }

    /**
     * Returns true if this spy was called after the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return boolean True if this spy was called after the supplied spy.
     */
    public function calledAfter(SpyInterface $spy)
    {
        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);
        if ($callCount < 1) {
            return false;
        }

        $otherCalls = $spy->recordedCalls();
        $otherCallCount = count($otherCalls);
        if ($otherCallCount < 1) {
            return false;
        }

        $lastCall = $calls[$callCount - 1];
        $otherFirstCall = $otherCalls[0];

        return $lastCall->sequenceNumber() > $otherFirstCall->sequenceNumber();

    }

    /**
     * Throws an exception unless this spy was called after the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledAfter(SpyInterface $spy)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            throw $this->assertionRecorder
                ->createFailure("Not called after supplied spy. Never called.");
        }

        $otherCalls = $spy->recordedCalls();
        $otherCallCount = count($otherCalls);

        if ($otherCallCount < 1) {
            throw $this->assertionRecorder->createFailure(
                "Not called after supplied spy. Supplied spy never called."
            );
        }

        $matchingCalls = array();

        if ($otherCallCount > 0) {
            $firstCall = $otherCalls[0];

            foreach ($calls as $call) {
                if ($call->sequenceNumber() > $firstCall->sequenceNumber()) {
                    $matchingCalls[] = $call;
                }
            }
        }

        if ($matchingCalls) {
            return $this->assertionRecorder->createSuccess($matchingCalls);
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Not called after supplied spy. Actual calls:\n%s",
                $this->assertionRenderer
                    ->renderCalls(static::mergeCalls(array($this->spy, $spy)))
            )
        );
    }

    /**
     * Returns true if called with the supplied arguments (and possibly others)
     * at least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if called with the supplied arguments at least once.
     */
    public function calledWith()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();;

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Throws an exception unless called with the supplied arguments (and
     * possibly others) at least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledWith()
    {
        $calls = $this->spy->recordedCalls();
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();;

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments like:\n    %s\nNever called.",
                    $this->assertionRenderer->renderMatchers($matchers)
                )
            );
        }

        $matchingCalls = array();
        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                $matchingCalls[] = $call;
            }
        }

        if ($matchingCalls) {
            return $this->assertionRecorder->createSuccess($matchingCalls);
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Expected arguments like:\n    %s\nActual calls:\n%s",
                $this->assertionRenderer->renderMatchers($matchers),
                $this->assertionRenderer->renderCallsArguments($calls)
            )
        );
    }

    /**
     * Returns true if always called with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if always called with the supplied arguments.
     */
    public function alwaysCalledWith()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();;

        foreach ($calls as $call) {
            if (
                !$this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Throws an exception unless always called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysCalledWith()
    {
        $calls = $this->spy->recordedCalls();
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();;

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected every call with arguments like:\n    %s\n" .
                        "Never called.",
                    $this->assertionRenderer->renderMatchers($matchers)
                )
            );
        }

        foreach ($calls as $call) {
            if (
                !$this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected every call with arguments like:\n    %s\n" .
                            "Actual calls:\n%s",
                        $this->assertionRenderer->renderMatchers($matchers),
                        $this->assertionRenderer->renderCallsArguments($calls)
                    )
                );
            }
        }

        return $this->assertionRecorder->createSuccess($calls);
    }

    /**
     * Returns true if called with the supplied arguments (and no others) at
     * least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if called with the supplied arguments at least once.
     */
    public function calledWithExactly()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Throws an exception unless called with the supplied arguments (and no
     * others) at least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledWithExactly()
    {
        $calls = $this->spy->recordedCalls();
        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments like:\n    %s\nNever called.",
                    $this->assertionRenderer->renderMatchers($matchers)
                )
            );
        }

        $matchingCalls = array();
        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                $matchingCalls[] = $call;
            }
        }

        if ($matchingCalls) {
            return $this->assertionRecorder->createSuccess($matchingCalls);
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Expected arguments like:\n    %s\nActual calls:\n%s",
                $this->assertionRenderer->renderMatchers($matchers),
                $this->assertionRenderer->renderCallsArguments($calls)
            )
        );
    }

    /**
     * Returns true if always called with the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if always called with the supplied arguments.
     */
    public function alwaysCalledWithExactly()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            if (
                !$this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Throws an exception unless always called with the supplied arguments (and
     * no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysCalledWithExactly()
    {
        $calls = $this->spy->recordedCalls();
        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected every call with arguments like:\n    %s\n" .
                        "Never called.",
                    $this->assertionRenderer->renderMatchers($matchers)
                )
            );
        }

        foreach ($calls as $call) {
            if (
                !$this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected every call with arguments like:\n    %s\n" .
                            "Actual calls:\n%s",
                        $this->assertionRenderer->renderMatchers($matchers),
                        $this->assertionRenderer->renderCallsArguments($calls)
                    )
                );
            }
        }

        return $this->assertionRecorder->createSuccess($calls);
    }

    /**
     * Returns true if never called with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if never called with the supplied arguments.
     */
    public function neverCalledWith()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return true;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();;

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Throws an exception unless never called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertNeverCalledWith()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return $this->assertionRecorder->createSuccess();
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();;

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected no call with arguments like:\n    %s\n" .
                            "Actual calls:\n%s",
                        $this->assertionRenderer->renderMatchers($matchers),
                        $this->assertionRenderer->renderCallsArguments($calls)
                    )
                );

            }
        }

        return $this->assertionRecorder->createSuccess();
    }

    /**
     * Returns true if never called with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if never called with the supplied arguments.
     */
    public function neverCalledWithExactly()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return true;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Throws an exception unless never called with the supplied arguments (and
     * no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertNeverCalledWithExactly()
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return $this->assertionRecorder->createSuccess();
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected no call with arguments like:\n    %s\n" .
                            "Actual calls:\n%s",
                        $this->assertionRenderer->renderMatchers($matchers),
                        $this->assertionRenderer->renderCallsArguments($calls)
                    )
                );

            }
        }

        return $this->assertionRecorder->createSuccess();
    }

    /**
     * Returns true if the $this value is the same as the supplied value for at
     * least one call.
     *
     * @param object|null $value The possible $this value.
     *
     * @return boolean True if the $this value is the same as the supplied value for at least one call.
     */
    public function calledOn($value)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        if ($this->matcherFactory->isMatcher($value)) {
            $isMatcher = true;
            $value = $this->matcherFactory->adapt($value);
        } else {
            $isMatcher = false;
        }

        foreach ($calls as $call) {
            $thisValue =
                $this->invocableInspector->callbackThisValue($call->callback());

            if ($isMatcher) {
                if ($value->matches($thisValue)) {
                    return true;
                }
            } elseif ($thisValue === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Throws an exception unless the $this value is the same as the supplied
     * value for at least one call.
     *
     * @param object|null $value The possible $this value.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledOn($value)
    {
        $calls = $this->spy->recordedCalls();

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);

            if (count($calls) < 1) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Not called on object like %s. Never called.',
                        $value->describe()
                    )
                );
            }

            $matchingCalls = array();
            foreach ($calls as $call) {
                if (
                    $value->matches(
                        $this->invocableInspector
                            ->callbackThisValue($call->callback())
                    )
                ) {
                    $matchingCalls[] = $call;
                }
            }

            if ($matchingCalls) {
                return $this->assertionRecorder->createSuccess($matchingCalls);
            }

            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Not called on object like %s. Actual objects:\n%s",
                    $value->describe(),
                    $this->assertionRenderer->renderThisValues($calls)
                )
            );
        }

        if (count($calls) < 1) {
            throw $this->assertionRecorder
                ->createFailure('Not called on expected object. Never called.');
        }

        $matchingCalls = array();
        foreach ($calls as $call) {
            if (
                $this->invocableInspector
                    ->callbackThisValue($call->callback()) === $value
            ) {
                $matchingCalls[] = $call;
            }
        }

        if ($matchingCalls) {
            return $this->assertionRecorder->createSuccess($matchingCalls);
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Not called on expected object. Actual objects:\n%s",
                $this->assertionRenderer->renderThisValues($calls)
            )
        );
    }

    /**
     * Returns true if the $this value is the same as the supplied value for
     * all calls.
     *
     * @param object|null $value The possible $this value.
     *
     * @return boolean True if the $this value is the same as the supplied value for all calls.
     */
    public function alwaysCalledOn($value)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        if ($this->matcherFactory->isMatcher($value)) {
            $isMatcher = true;
            $value = $this->matcherFactory->adapt($value);
        } else {
            $isMatcher = false;
        }

        foreach ($calls as $call) {
            $thisValue =
                $this->invocableInspector->callbackThisValue($call->callback());

            if ($isMatcher) {
                if (!$value->matches($thisValue)) {
                    return false;
                }
            } elseif ($thisValue !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Throws an exception unless the $this value is the same as the supplied
     * value for all calls.
     *
     * @param object|null $value The possible $this value.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysCalledOn($value)
    {
        $calls = $this->spy->recordedCalls();

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);

            if (count($calls) < 1) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Not called on object like %s. Never called.',
                        $value->describe()
                    )
                );
            }

            foreach ($calls as $call) {
                if (
                    !$value->matches(
                        $this->invocableInspector
                            ->callbackThisValue($call->callback())
                    )
                ) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            "Not always called on object like %s. " .
                                "Actual objects:\n%s",
                            $value->describe(),
                            $this->assertionRenderer->renderThisValues($calls)
                        )
                    );
                }
            }

            return $this->assertionRecorder->createSuccess($calls);
        }

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                'Not called on expected object. Never called.'
            );
        }

        foreach ($calls as $call) {
            if (
                $this->invocableInspector
                    ->callbackThisValue($call->callback()) !== $value
            ) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Not always called on expected object. " .
                            "Actual objects:\n%s",
                        $this->assertionRenderer->renderThisValues($calls)
                    )
                );

            }
        }

        return $this->assertionRecorder->createSuccess($calls);
    }

    /**
     * Returns true if this spy returned the supplied value at least once.
     *
     * @param mixed $value The value.
     *
     * @return boolean True if this spy returned the supplied value at least once.
     */
    public function returned($value)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        $value = $this->matcherFactory->adapt($value);

        foreach ($calls as $call) {
            if (!$call->exception() && $value->matches($call->returnValue())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Throws an exception unless this spy returned the supplied value at least
     * once.
     *
     * @param mixed $value The value.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertReturned($value)
    {
        $calls = $this->spy->recordedCalls();
        $value = $this->matcherFactory->adapt($value);

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected return value like %s. Never called.',
                    $value->describe()
                )
            );
        }

        $matchingEvents = array();

        foreach ($calls as $call) {
            if (!$call->exception() && $value->matches($call->returnValue())) {
                $matchingEvents[] = $call->responseEvent();
            }
        }

        if ($matchingEvents) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                "Expected return value like %s. Actually returned:\n%s",
                $value->describe(),
                $this->assertionRenderer->renderReturnValues($calls)
            )
        );
    }

    /**
     * Returns true if this spy always returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return boolean True if this spy always returned the supplied value.
     */
    public function alwaysReturned($value)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        $value = $this->matcherFactory->adapt($value);

        foreach ($calls as $call) {
            if (!$value->matches($call->returnValue())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Throws an exception unless this spy always returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysReturned($value)
    {
        $calls = $this->spy->recordedCalls();
        $value = $this->matcherFactory->adapt($value);

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected return value like %s. Never called.',
                    $value->describe()
                )
            );
        }

        $matchingEvents = array();

        foreach ($calls as $call) {
            if ($value->matches($call->returnValue())) {
                $matchingEvents[] = $call->responseEvent();
            } else {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected every call with return value like %s. " .
                            "Actually returned:\n%s",
                        $value->describe(),
                        $this->assertionRenderer->renderReturnValues($calls)
                    )
                );
            }
        }

        return $this->assertionRecorder->createSuccess($matchingEvents);
    }

    /**
     * Returns true if an exception of the supplied type was thrown at least
     * once.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean True if a matching exception was thrown at least once.
     */
    public function threw($type = null)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        if (null === $type) {
            $typeType = 'null';
        } elseif (is_string($type)) {
            $typeType = 'string';
        } elseif (is_object($type) && $this->matcherFactory->isMatcher($type)) {
            $typeType = 'matcher';
            $type = $this->matcherFactory->adapt($type);
        } elseif ($type instanceof Exception) {
            $typeType = 'exception';
        } else {
            $typeType = 'unknown';
        }

        foreach ($calls as $call) {
            $exception = $call->exception();

            if (!$exception) {
                continue;
            }

            switch ($typeType) {
                case 'null':
                    return true;

                case 'string':
                    if (is_a($exception, $type)) {
                        return true;
                    }

                    continue 2;

                case 'matcher':
                    if ($type->matches($exception)) {
                        return true;
                    }

                    continue 2;

                case 'exception':
                    if ($exception == $type) {
                        return true;
                    }
            }
        }

        return false;
    }

    /**
     * Throws an exception unless an exception of the supplied type was thrown
     * at least once.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertThrew($type = null)
    {
        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);

        if (null === $type) {
            if ($callCount < 1) {
                throw $this->assertionRecorder
                    ->createFailure('Nothing thrown. Never called.');
            }

            $matchingEvents = array();

            foreach ($calls as $call) {
                if ($call->exception()) {
                    $matchingEvents[] = $call->responseEvent();
                }
            }

            if ($matchingEvents) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            throw $this->assertionRecorder->createFailure(
                sprintf('Nothing thrown in %d call(s).', $callCount)
            );
        } elseif (is_string($type)) {
            if ($callCount < 1) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected %s exception. Never called.',
                        $this->assertionRenderer->renderValue($type)
                    )
                );
            }

            $isAnyExceptions = false;
            $matchingEvents = array();

            foreach ($calls as $call) {
                $exception = $call->exception();

                if (!$exception) {
                    continue;
                }

                if (is_a($exception, $type)) {
                    $matchingEvents[] = $call->responseEvent();
                }

                if ($exception) {
                    $isAnyExceptions = true;
                }
            }

            if (!$isAnyExceptions) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected %s exception. Nothing thrown in %d call(s).',
                        $this->assertionRenderer->renderValue($type),
                        $callCount
                    )
                );
            }

            if ($matchingEvents) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected %s exception. Actually threw:\n%s",
                    $this->assertionRenderer->renderValue($type),
                    $this->assertionRenderer->renderThrownExceptions($calls)
                )
            );
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                if ($callCount < 1) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception equal to %s. Never called.',
                            $this->assertionRenderer->renderException($type)
                        )
                    );
                }

                $isAnyExceptions = false;
                $matchingEvents = array();

                foreach ($calls as $call) {
                    $exception = $call->exception();

                    if (!$exception) {
                        continue;
                    }

                    if ($exception == $type) {
                        $matchingEvents[] = $call->responseEvent();
                    }

                    if ($exception) {
                        $isAnyExceptions = true;
                    }
                }

                if (!$isAnyExceptions) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception equal to %s. ' .
                                'Nothing thrown in %d call(s).',
                            $this->assertionRenderer->renderException($type),
                            $callCount
                        )
                    );
                }

                if ($matchingEvents) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }

                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected exception equal to %s. " .
                            "Actually threw:\n%s",
                        $this->assertionRenderer->renderException($type),
                        $this->assertionRenderer->renderThrownExceptions($calls)
                    )
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $type = $this->matcherFactory->adapt($type);

                if ($callCount < 1) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception like %s. Never called.',
                            $type->describe()
                        )
                    );
                }

                $isAnyExceptions = false;
                $matchingEvents = array();

                foreach ($calls as $call) {
                    $exception = $call->exception();

                    if (!$exception) {
                        continue;
                    }

                    if ($type->matches($call->exception())) {
                        $matchingEvents[] = $call->responseEvent();
                    }

                    if ($exception) {
                        $isAnyExceptions = true;
                    }
                }

                if (!$isAnyExceptions) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception like %s. ' .
                                'Nothing thrown in %d call(s).',
                            $type->describe(),
                            $callCount
                        )
                    );
                }

                if ($matchingEvents) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }

                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected exception like %s. Actually threw:\n%s",
                        $type->describe(),
                        $this->assertionRenderer->renderThrownExceptions($calls)
                    )
                );
            }
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Unable to match exceptions against %s.',
                $this->assertionRenderer->renderValue($type)
            )
        );
    }

    /**
     * Returns true if an exception of the supplied type was always thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean True if a matching exception was always thrown.
     */
    public function alwaysThrew($type = null)
    {
        $calls = $this->spy->recordedCalls();

        if (count($calls) < 1) {
            return false;
        }

        if (null === $type) {
            $typeType = 'null';
        } elseif (is_string($type)) {
            $typeType = 'string';
        } elseif (is_object($type) && $this->matcherFactory->isMatcher($type)) {
            $typeType = 'matcher';
            $type = $this->matcherFactory->adapt($type);
        } elseif ($type instanceof Exception) {
            $typeType = 'exception';
        } else {
            $typeType = 'unknown';
        }

        foreach ($calls as $call) {
            $exception = $call->exception();

            if (!$exception) {
                return false;
            }

            switch ($typeType) {
                case 'null':
                    continue 2;

                case 'string':
                    if (is_a($exception, $type)) {
                        continue 2;
                    }

                    break;

                case 'matcher':
                    if ($type->matches($exception)) {
                        continue 2;
                    }

                    break;

                case 'exception':
                    if ($exception == $type) {
                        continue 2;
                    }
            }

            return false;
        }

        return true;
    }

    /**
     * Throws an exception unless an exception of the supplied type was always
     * thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return AssertionResultInterface If the assertion passes.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysThrew($type = null)
    {
        $calls = $this->spy->recordedCalls();
        $callCount = count($calls);

        if (null === $type) {
            if ($callCount < 1) {
                throw $this->assertionRecorder
                    ->createFailure('Nothing thrown. Never called.');
            }

            $matchingEvents = array();
            $allMatch = true;

            foreach ($calls as $call) {
                if ($call->exception()) {
                    $matchingEvents[] = $call->responseEvent();
                } else {
                    $allMatch = false;
                }
            }

            if (!$matchingEvents) {
                throw $this->assertionRecorder->createFailure(
                    sprintf('Nothing thrown in %d call(s).', $callCount)
                );
            }

            if ($allMatch) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected every call to throw. Actually threw:\n%s",
                    $this->assertionRenderer->renderThrownExceptions($calls)
                )
            );
        } elseif (is_string($type)) {
            if ($callCount < 1) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected %s exception. Never called.',
                        $this->assertionRenderer->renderValue($type)
                    )
                );
            }

            $isAnyExceptions = false;
            $matchingEvents = array();
            $allMatch = true;

            foreach ($calls as $call) {
                $exception = $call->exception();

                if (is_a($exception, $type)) {
                    $matchingEvents[] = $call->responseEvent();
                } else {
                    $allMatch = false;
                }

                if ($exception) {
                    $isAnyExceptions = true;
                }
            }

            if (!$isAnyExceptions) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected %s exception. Nothing thrown in %d call(s).',
                        $this->assertionRenderer->renderValue($type),
                        $callCount
                    )
                );
            }

            if ($allMatch) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected every call to throw %s exception. " .
                        "Actually threw:\n%s",
                    $this->assertionRenderer->renderValue($type),
                    $this->assertionRenderer->renderThrownExceptions($calls)
                )
            );
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                if ($callCount < 1) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception equal to %s. Never called.',
                            $this->assertionRenderer->renderException($type)
                        )
                    );
                }

                $isAnyExceptions = false;
                $matchingEvents = array();
                $allMatch = true;

                foreach ($calls as $call) {
                    $exception = $call->exception();

                    if ($exception == $type) {
                        $matchingEvents[] = $call->responseEvent();
                    } else {
                        $allMatch = false;
                    }

                    if ($exception) {
                        $isAnyExceptions = true;
                    }
                }

                if (!$isAnyExceptions) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception equal to %s. ' .
                                'Nothing thrown in %d call(s).',
                            $this->assertionRenderer->renderException($type),
                            $callCount
                        )
                    );
                }

                if ($allMatch) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }

                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected every call to throw exception equal to" .
                            " %s. Actually threw:\n%s",
                        $this->assertionRenderer->renderException($type),
                        $this->assertionRenderer->renderThrownExceptions($calls)
                    )
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $type = $this->matcherFactory->adapt($type);

                if ($callCount < 1) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception like %s. Never called.',
                            $type->describe()
                        )
                    );
                }

                $isAnyExceptions = false;
                $matchingEvents = array();
                $allMatch = true;

                foreach ($calls as $call) {
                    $exception = $call->exception();

                    if ($exception && $type->matches($exception)) {
                        $matchingEvents[] = $call->responseEvent();
                    } else {
                        $allMatch = false;
                    }

                    if ($exception) {
                        $isAnyExceptions = true;
                    }
                }

                if (!$isAnyExceptions) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception like %s. ' .
                                'Nothing thrown in %d call(s).',
                            $type->describe(),
                            $callCount
                        )
                    );
                }

                if ($allMatch) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }

                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected every call to throw exception like %s. " .
                            "Actually threw:\n%s",
                        $type->describe(),
                        $this->assertionRenderer->renderThrownExceptions($calls)
                    )
                );
            }
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Unable to match exceptions against %s.',
                $this->assertionRenderer->renderValue($type)
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
