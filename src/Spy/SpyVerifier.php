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
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Spy\Exception\UndefinedCallException;
use Exception;
use SebastianBergmann\Exporter\Exporter;

/**
 * Provides convenience methods for verifying interactions with a spy.
 */
class SpyVerifier implements SpyVerifierInterface
{
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
     * @param Exporter|null                     $exporter            The exporter to use.
     */
    public function __construct(
        SpyInterface $spy = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        CallVerifierFactoryInterface $callVerifierFactory = null,
        AssertionRecorderInterface $assertionRecorder = null,
        Exporter $exporter = null
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
        if (null === $exporter) {
            $exporter = new Exporter();
        }

        $this->spy = $spy;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->exporter = $exporter;
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
     * Get the exporter.
     *
     * @return Exporter The exporter.
     */
    public function exporter()
    {
        return $this->exporter;
    }

    /**
     * Returns true if this spy has a subject.
     *
     * @return boolean True if this spy has a subject.
     */
    public function hasSubject()
    {
        return $this->spy->hasSubject();
    }

    /**
     * Get the subject.
     *
     * @return callable                  The subject.
     * @throws UndefinedSubjectException If there is no subject.
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
     * @param mixed $arguments,...
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function __invoke()
    {
        return call_user_func_array($this->spy, func_get_args());
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
            throw $this->assertionRecorder->createFailure(
                'Expected the spy to be called at least once. The spy was ' .
                    'never called.'
            );
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
                sprintf(
                    'Expected the spy to be called once. The spy was ' .
                        'actually called %d time(s).',
                    $callCount
                )
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
                    'Expected the spy to be called %d time(s). The spy was ' .
                        'actually called %d time(s).',
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

        return $firstCall->sequenceNumber() <
            $otherLastCall->sequenceNumber();
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
                    "The spy was not called before the supplied spy. The " .
                        "actual call order was:\n%s",
                    $this->renderCalls($this->callsBySequence($this->spy, $spy))
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

        return $lastCall->sequenceNumber() >
            $otherFirstCall->sequenceNumber();

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
                    "The spy was not called after the supplied spy. The " .
                        "actual call order was:\n%s",
                    $this->renderCalls($this->callsBySequence($this->spy, $spy))
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
        $matchers[] = WildcardMatcher::instance();

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
        $matchers[] = WildcardMatcher::instance();

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
        $matchers[] = WildcardMatcher::instance();

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

        foreach ($calls as $call) {
            if ($call->thisValue() === $value) {
                return true;
            }
        }

        return false;
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

        foreach ($calls as $call) {
            if ($call->thisValue() !== $value) {
                return false;
            }
        }

        return true;
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

        foreach ($calls as $call) {
            if ($call->returnValue() === $value) {
                return true;
            }
        }

        return false;
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

        foreach ($calls as $call) {
            if ($call->returnValue() !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if an exception of the supplied type was thrown at least
     * once.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean                   True if a matching exception was thrown at least once.
     * @throws UndefinedSubjectException If there is no subject.
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
     * Returns true if an exception of the supplied type was always thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean                   True if a matching exception was always thrown.
     * @throws UndefinedSubjectException If there is no subject.
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
     * Get a sorted sequence of calls for one or more spies.
     *
     * @param SpyInterface $spies,... The spies.
     *
     * @return array<integer,CallInterface> The calls, sorted by call order.
     */
    public function callsBySequence()
    {
        $calls = array();

        foreach (func_get_args() as $spy) {
            if ($spy instanceof SpyVerifierInterface) {
                $spy = $spy->spy();
            }

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
     * Render a sequence of calls.
     *
     * @param array<integer,CallInterface> $calls The calls.
     *
     * @return string The rendered calls.
     */
    protected function renderCalls(array $calls)
    {
        $rendered = array();
        foreach ($calls as $call) {
            $rendered[] = sprintf('    - %s', $this->renderCall($call));
        }

        return implode("\n", $rendered);
    }

    /**
     * Render a call.
     *
     * @param CallInterface $call The call.
     *
     * @return string The rendered call.
     */
    protected function renderCall(CallInterface $call)
    {
        $arguments = $call->arguments();

        if (count($arguments) < 1) {
            return '<no arguments>';
        }

        $rendered = array();
        foreach ($arguments as $argument) {
            $rendered[] = $this->exporter->export($argument);
        }

        return implode(', ', $rendered);
    }

    private $spy;
    private $matcherFactory;
    private $matcherVerifier;
    private $callVerifierFactory;
    private $assertionRecorder;
    private $exporter;
}
