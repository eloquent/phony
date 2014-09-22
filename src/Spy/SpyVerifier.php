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

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Exception;

/**
 * Provides convenience methods for verifying interactions with a spy.
 */
class SpyVerifier implements SpyVerifierInterface
{
    /**
     * Construct a new spy verifier.
     *
     * @param SpyInterface|null                 $spy                 The spy.
     * @param MatcherFactoryInterface|null      $matcherFactory      The matcher factory to use.
     * @param CallVerifierFactoryInterface|null $callVerifierFactory The call verifier factory to use.
     */
    public function __construct(
        SpyInterface $spy = null,
        MatcherFactoryInterface $matcherFactory = null,
        CallVerifierFactoryInterface $callVerifierFactory = null
    ) {
        if (null === $spy) {
            $spy = new Spy();
        }
        if (null === $matcherFactory) {
            $matcherFactory = new MatcherFactory();
        }
        if (null === $callVerifierFactory) {
            $callVerifierFactory = CallVerifierFactory::instance();
        }

        $this->spy = $spy;
        $this->matcherFactory = $matcherFactory;
        $this->callVerifierFactory = $callVerifierFactory;
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
     * Get the call verifier factory.
     *
     * @return CallVerifierFactoryInterface The call verifier factory.
     */
    public function callVerifierFactory()
    {
        return $this->callVerifierFactory;
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

        return $calls[$index];
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

        return $calls[0];
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

        return $calls[$index];
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
     * Returns true if called only once.
     *
     * @return boolean True if called only once.
     */
    public function calledOnce()
    {
        return 1 === count($this->spy->calls());
    }

    /**
     * Returns true if called an exact amount of times.
     *
     * @return boolean True if called an exact amount of times.
     */
    public function calledTimes($times)
    {
        return $times === count($this->spy->calls());
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

        if (0 == func_num_args()) {
            return true;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            $arguments = $call->arguments();

            foreach ($matchers as $index => $matcher) {
                if (
                    !array_key_exists($index, $arguments) ||
                    !$matcher->matches($arguments[$index])
                ) {
                    continue 2;
                }
            }

            return true;
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

        if (0 == func_num_args()) {
            return true;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            $arguments = $call->arguments();

            foreach ($matchers as $index => $matcher) {
                if (
                    !array_key_exists($index, $arguments) ||
                    !$matcher->matches($arguments[$index])
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns true if called with the supplied arguments and no others at least
     * once.
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
            $arguments = $call->arguments();

            if (count($arguments) !== count($matchers)) {
                continue;
            }

            foreach ($matchers as $index => $matcher) {
                if (
                    !array_key_exists($index, $arguments) ||
                    !$matcher->matches($arguments[$index])
                ) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Returns true if always called with the supplied arguments and no others.
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
            $arguments = $call->arguments();

            if (count($arguments) !== count($matchers)) {
                return false;
            }

            foreach ($matchers as $index => $matcher) {
                if (
                    !array_key_exists($index, $arguments) ||
                    !$matcher->matches($arguments[$index])
                ) {
                    return false;
                }
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

        if (0 == func_num_args()) {
            return false;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            $arguments = $call->arguments();

            foreach ($matchers as $index => $matcher) {
                if (
                    !array_key_exists($index, $arguments) ||
                    !$matcher->matches($arguments[$index])
                ) {
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Returns true if never called with the supplied arguments and no others.
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

        if (0 == func_num_args()) {
            return false;
        }

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        foreach ($calls as $call) {
            $arguments = $call->arguments();

            if (count($arguments) !== count($matchers)) {
                continue;
            }

            foreach ($matchers as $index => $matcher) {
                if (
                    !array_key_exists($index, $arguments) ||
                    !$matcher->matches($arguments[$index])
                ) {
                    continue 2;
                }
            }

            return false;
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

        if ($type instanceof Exception) {
            $type = $this->matcherFactory->equalTo($type);
        }

        foreach ($calls as $call) {
            $exception = $call->exception();
            if (null === $exception) {
                continue;
            }

            if (null === $type) {
                return true;
            }

            if (
                $type instanceof MatcherInterface && $type->matches($exception)
            ) {
                return true;
            }

            if ($exception instanceof $type) {
                return true;
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

        if ($type instanceof Exception) {
            $type = $this->matcherFactory->equalTo($type);
        }

        foreach ($calls as $call) {
            $exception = $call->exception();
            if (null === $exception) {
                return false;
            }

            if (null === $type) {
                continue;
            }

            if (
                $type instanceof MatcherInterface && $type->matches($exception)
            ) {
                continue;
            }

            if ($exception instanceof $type) {
                continue;
            }

            return false;
        }

        return true;
    }

    private $spy;
    private $matcherFactory;
    private $callVerifierFactory;
}
