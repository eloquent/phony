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

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Invocable\InvocableUtils;
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
            foreach ($spy->calls() as $call) {
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
     */
    public function __construct(
        SpyInterface $spy = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        CallVerifierFactoryInterface $callVerifierFactory = null,
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null
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

        $this->spy = $spy;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
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
     * Get the subject.
     *
     * @return callable The subject.
     */
    public function subject()
    {
        return $this->spy->subject();
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
     * Get the calls.
     *
     * @return array<CallVerifierInterface> The calls.
     */
    public function calls()
    {
        return $this->callVerifierFactory->adaptAll($this->spy->calls());
    }

    /**
     * Record a call by invocation.
     *
     * This method supports reference parameters.
     *
     * @param array<integer,mixed>|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function invokeWith(array $arguments = null)
    {
        return $this->spy->invokeWith($arguments);
    }

    /**
     * Record a call by invocation.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function invoke()
    {
        return $this->spy->invokeWith(func_get_args());
    }

    /**
     * Record a call by invocation.
     *
     * @param mixed $arguments,...
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
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
        return count($this->spy->calls());
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
        $calls = $this->spy->calls();
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
        $calls = $this->spy->calls();
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
        $callCount = count($this->spy->calls());
        if ($callCount > 0) {
            $index = $callCount - 1;
        } else {
            $index = 0;
        }

        $calls = $this->spy->calls();
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
        return count($this->spy->calls()) > 0;
    }

    /**
     * Throws an exception unless called at least once.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalled()
    {
        if (count($this->spy->calls()) < 1) {
            throw $this->assertionRecorder->createFailure('Never called.');
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if called only once.
     *
     * @return boolean True if called only once.
     */
    public function calledOnce()
    {
        return 1 === count($this->spy->calls());
    }

    /**
     * Throws an exception unless called only once.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalledOnce()
    {
        $callCount = count($this->spy->calls());

        if (1 !== $callCount) {
            throw $this->assertionRecorder->createFailure(
                sprintf('Expected 1 call. Called %d time(s).', $callCount)
            );
        }

        $this->assertionRecorder->recordSuccess();
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
        return $times === count($this->spy->calls());
    }

    /**
     * Throws an exception unless called an exact amount of times.
     *
     * @param integer $times The expected number of calls.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalledTimes($times)
    {
        $callCount = count($this->spy->calls());

        if ($times !== $callCount) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected %d call(s). Called %d time(s).',
                    $times,
                    $callCount
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();
        $callCount = count($calls);
        if ($callCount < 1) {
            return false;
        }

        $otherCalls = $spy->calls();
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
     * @throws Exception If the assertion fails.
     */
    public function assertCalledBefore(SpyInterface $spy)
    {
        if (!$this->calledBefore($spy)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Not called before supplied spy. Actual calls:\n%s",
                    $this->assertionRenderer->renderCalls(
                        static::mergeCalls(array($this->spy, $spy))
                    )
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();
        $callCount = count($calls);
        if ($callCount < 1) {
            return false;
        }

        $otherCalls = $spy->calls();
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
     * @throws Exception If the assertion fails.
     */
    public function assertCalledAfter(SpyInterface $spy)
    {
        if (!$this->calledAfter($spy)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Not called after supplied spy. Actual calls:\n%s",
                    $this->assertionRenderer->renderCalls(
                        static::mergeCalls(array($this->spy, $spy))
                    )
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

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
     * @throws Exception If the assertion fails.
     */
    public function assertCalledWith()
    {
        $calls = $this->spy->calls();
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

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return $this->assertionRecorder->recordSuccess();
            }
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
        $calls = $this->spy->calls();

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
     * @throws Exception If the assertion fails.
     */
    public function assertAlwaysCalledWith()
    {
        $calls = $this->spy->calls();
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

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

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
     * @throws Exception If the assertion fails.
     */
    public function assertCalledWithExactly()
    {
        $calls = $this->spy->calls();
        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments like:\n    %s\nNever called.",
                    $this->assertionRenderer->renderMatchers($matchers)
                )
            );
        }

        foreach ($calls as $call) {
            if (
                $this->matcherVerifier->matches($matchers, $call->arguments())
            ) {
                return $this->assertionRecorder->recordSuccess();
            }
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
        $calls = $this->spy->calls();

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
     * @throws Exception If the assertion fails.
     */
    public function assertAlwaysCalledWithExactly()
    {
        $calls = $this->spy->calls();
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

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

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
     * @throws Exception If the assertion fails.
     */
    public function assertNeverCalledWith()
    {
        $calls = $this->spy->calls();

        if (count($calls) < 1) {
            return $this->assertionRecorder->recordSuccess();
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

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

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
     * @throws Exception If the assertion fails.
     */
    public function assertNeverCalledWithExactly()
    {
        $calls = $this->spy->calls();

        if (count($calls) < 1) {
            return $this->assertionRecorder->recordSuccess();
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

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

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
            $thisValue = InvocableUtils::callbackThisValue($call->callback());

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
     * @throws Exception If the assertion fails.
     */
    public function assertCalledOn($value)
    {
        $calls = $this->spy->calls();

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
                    $value->matches(
                        InvocableUtils::callbackThisValue($call->callback())
                    )
                ) {
                    return $this->assertionRecorder->recordSuccess();
                }
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

        foreach ($calls as $call) {
            if (
                InvocableUtils::callbackThisValue($call->callback()) === $value
            ) {
                return $this->assertionRecorder->recordSuccess();
            }
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
        $calls = $this->spy->calls();

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
            $thisValue = InvocableUtils::callbackThisValue($call->callback());

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
     * @throws Exception If the assertion fails.
     */
    public function assertAlwaysCalledOn($value)
    {
        $calls = $this->spy->calls();

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
                        InvocableUtils::callbackThisValue($call->callback())
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

            return $this->assertionRecorder->recordSuccess();
        }

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                'Not called on expected object. Never called.'
            );
        }

        foreach ($calls as $call) {
            if (
                InvocableUtils::callbackThisValue($call->callback()) !== $value
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

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

        if (count($calls) < 1) {
            return false;
        }

        $value = $this->matcherFactory->adapt($value);

        foreach ($calls as $call) {
            if ($value->matches($call->returnValue())) {
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
     * @throws Exception If the assertion fails.
     */
    public function assertReturned($value)
    {
        $calls = $this->spy->calls();
        $value = $this->matcherFactory->adapt($value);

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected return value like %s. Never called.',
                    $value->describe()
                )
            );
        }

        foreach ($calls as $call) {
            if ($value->matches($call->returnValue())) {
                return $this->assertionRecorder->recordSuccess();
            }
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
        $calls = $this->spy->calls();

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
     * @throws Exception If the assertion fails.
     */
    public function assertAlwaysReturned($value)
    {
        $calls = $this->spy->calls();
        $value = $this->matcherFactory->adapt($value);

        if (count($calls) < 1) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected return value like %s. Never called.',
                    $value->describe()
                )
            );
        }

        foreach ($calls as $call) {
            if (!$value->matches($call->returnValue())) {
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

        $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

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

            switch ($typeType) {
                case 'null':
                    if (null !== $exception) {
                        return true;
                    }

                    continue 2;

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
     * @throws Exception If the assertion fails.
     */
    public function assertThrew($type = null)
    {
        $calls = $this->spy->calls();
        $callCount = count($calls);

        if (null === $type) {
            if ($callCount < 1) {
                throw $this->assertionRecorder
                    ->createFailure('Nothing thrown. Never called.');
            }

            foreach ($calls as $call) {
                if (null !== $call->exception()) {
                    return $this->assertionRecorder->recordSuccess();
                }
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
            $isMatch = false;
            foreach ($calls as $call) {
                $exception = $call->exception();
                $isMatch = $isMatch || is_a($exception, $type);

                if (null !== $exception) {
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

            if (!$isMatch) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected %s exception. Actually threw:\n%s",
                        $this->assertionRenderer->renderValue($type),
                        $this->assertionRenderer->renderThrownExceptions($calls)
                    )
                );
            }

            return $this->assertionRecorder->recordSuccess();
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
                $isMatch = false;
                foreach ($calls as $call) {
                    $exception = $call->exception();
                    $isMatch = $isMatch || $exception == $type;

                    if (null !== $exception) {
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

                if (!$isMatch) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            "Expected exception equal to %s. " .
                                "Actually threw:\n%s",
                            $this->assertionRenderer->renderException($type),
                            $this->assertionRenderer
                                ->renderThrownExceptions($calls)
                        )
                    );
                }

                return $this->assertionRecorder->recordSuccess();
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
                $isMatch = false;
                foreach ($calls as $call) {
                    $exception = $call->exception();
                    $isMatch = $isMatch || $type->matches($call->exception());

                    if (null !== $exception) {
                        $isAnyExceptions = true;
                    }
                }

                if (!$isMatch && !$isAnyExceptions) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception like %s. ' .
                                'Nothing thrown in %d call(s).',
                            $type->describe(),
                            $callCount
                        )
                    );
                }

                if (!$isMatch) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            "Expected exception like %s. Actually threw:\n%s",
                            $type->describe(),
                            $this->assertionRenderer
                                ->renderThrownExceptions($calls)
                        )
                    );
                }

                return $this->assertionRecorder->recordSuccess();
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
        $calls = $this->spy->calls();

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

            switch ($typeType) {
                case 'null':
                    if (null !== $exception) {
                        continue 2;
                    }

                    break;

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
     * @throws Exception If the assertion fails.
     */
    public function assertAlwaysThrew($type = null)
    {
        $calls = $this->spy->calls();
        $callCount = count($calls);

        if (null === $type) {
            if ($callCount < 1) {
                throw $this->assertionRecorder
                    ->createFailure('Nothing thrown. Never called.');
            }

            $isMatch = true;
            foreach ($calls as $call) {
                $isMatch = $isMatch && null !== $call->exception();
            }

            if (!$isMatch) {
                throw $this->assertionRecorder->createFailure(
                    sprintf('Nothing thrown in %d call(s).', $callCount)
                );
            }

            return $this->assertionRecorder->recordSuccess();
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
            $isMatch = true;
            foreach ($calls as $call) {
                $exception = $call->exception();
                $isMatch = $isMatch && is_a($exception, $type);

                if (null !== $exception) {
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

            if (!$isMatch) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        "Expected every call to throw %s exception. " .
                            "Actually threw:\n%s",
                        $this->assertionRenderer->renderValue($type),
                        $this->assertionRenderer->renderThrownExceptions($calls)
                    )
                );
            }

            return $this->assertionRecorder->recordSuccess();
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
                $isMatch = true;
                foreach ($calls as $call) {
                    $exception = $call->exception();
                    $isMatch = $isMatch && $exception == $type;

                    if (null !== $exception) {
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

                if (!$isMatch) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            "Expected every call to throw exception equal to" .
                                " %s. Actually threw:\n%s",
                            $this->assertionRenderer->renderException($type),
                            $this->assertionRenderer
                                ->renderThrownExceptions($calls)
                        )
                    );
                }

                return $this->assertionRecorder->recordSuccess();
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
                $isMatch = true;
                foreach ($calls as $call) {
                    $exception = $call->exception();
                    $isMatch = $isMatch && $type->matches($call->exception());

                    if (null !== $exception) {
                        $isAnyExceptions = true;
                    }
                }

                if (!$isMatch && !$isAnyExceptions) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception like %s. ' .
                                'Nothing thrown in %d call(s).',
                            $type->describe(),
                            $callCount
                        )
                    );
                }

                if (!$isMatch) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            "Expected every call to throw exception like %s. " .
                                "Actually threw:\n%s",
                            $type->describe(),
                            $this->assertionRenderer
                                ->renderThrownExceptions($calls)
                        )
                    );
                }

                return $this->assertionRecorder->recordSuccess();
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
}
