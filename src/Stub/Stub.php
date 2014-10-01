<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Exception;

/**
 * Provides canned answers to function or method invocations.
 *
 * @internal
 */
class Stub implements StubInterface
{
    /**
     * Construct a new stub.
     *
     * @param object|null                   $thisValue       The $this value.
     * @param MatcherFactoryInterface|null  $matcherFactory  The matcher factory to use.
     * @param MatcherVerifierInterface|null $matcherVerifier The matcher verifier to use.
     */
    public function __construct(
        $thisValue = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }

        $this->thisValue = $thisValue;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->matchers = array($this->matcherFactory->wildcard());
        $this->callbacks = array();
        $this->isNewRule = true;
        $this->rules = array();
        $this->ruleCounts = array();
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
     * Set the $this value of this stub.
     *
     * This value is used by returnsThis().
     *
     * @return object|null The $this value, or null to use the stub object.
     */
    public function setThisValue($thisValue)
    {
        $this->thisValue = $thisValue;
    }

    /**
     * Get the $this value of this stub.
     *
     * @return object The $this value.
     */
    public function thisValue()
    {
        if ($this->thisValue) {
            return $this->thisValue;
        }

        return $this;
    }

    /**
     * Modify the current criteria to match the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function with()
    {
        if ($this->isNewRule) {
            $this->returns();
        }

        $this->isNewRule = true;
        $this->matchers = $this->matcherFactory->adaptAll(func_get_args());
        $this->matchers[] = $this->matcherFactory->wildcard();

        return $this;
    }

    /**
     * Modify the current criteria to match the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function withExactly()
    {
        if ($this->isNewRule) {
            $this->returns();
        }

        $this->isNewRule = true;
        $this->matchers = $this->matcherFactory->adaptAll(func_get_args());

        return $this;
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable $callback                The callback.
     * @param callable $additionalCallbacks,... Additional callbacks for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function does($callback)
    {
        if ($this->isNewRule) {
            $this->isNewRule = false;

            array_unshift($this->rules, array($this->matchers, array()));
            array_unshift($this->ruleCounts, 0);
        }

        foreach (func_get_args() as $callback) {
            array_push($this->rules[0][1], array($callback, $this->callbacks));
            $this->callbacks = array();
        }

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable $callback      The callback.
     * @param mixed    $arguments,... The arguments to call the callback with.
     *
     * @return StubInterface This stub.
     */
    public function calls($callback)
    {
        $arguments = func_get_args();

        return $this->callsWith(array_shift($arguments), $arguments);
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * This method supports reference parameters.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable                  $callback        The callback.
     * @param array<integer,mixed>|null $arguments       The arguments to call the callback with.
     * @param boolean|null              $appendArguments True if the invocation arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function callsWith(
        $callback,
        array $arguments = null,
        $appendArguments = null
    ) {
        if (null === $arguments) {
            $arguments = array();
        }
        if (null === $appendArguments) {
            $appendArguments = false;
        }

        array_push(
            $this->callbacks,
            array($callback, $arguments, $appendArguments)
        );

        return $this;
    }

    /**
     * Add an answer that returns a value.
     *
     * @param mixed $value                The return value.
     * @param mixed $additionalValues,... Additional return values for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function returns($value = null)
    {
        if (0 === func_num_args()) {
            return $this->does(function () {});
        }

        foreach (func_get_args() as $value) {
            $this->does(
                function () use ($value) {
                    return $value;
                }
            );
        }

        return $this;
    }

    /**
     * Add an answer that returns an argument.
     *
     * Negative indices are equivalent to $argumentCount - $index.
     *
     * @param integer|null $index The argument index, or null to return the first argument.
     *
     * @return StubInterface This stub.
     */
    public function returnsArgument($index = null)
    {
        if (null === $index) {
            $index = 0;
        }

        return $this->does(
            function () use ($index) {
                $argumentCount = func_num_args();

                if ($argumentCount < 1) {
                    return null;
                }

                if ($index < 0) {
                    $index = $argumentCount + $index;
                }

                if ($index >= $argumentCount) {
                    return null;
                }

                return func_get_arg($index);
            }
        );
    }

    /**
     * Add an answer that returns the $this value.
     *
     * @return StubInterface This stub.
     */
    public function returnsThis()
    {
        $stub = $this;

        return $this->does(
            function () use ($stub) {
                return $stub->thisValue();
            }
        );
    }

    /**
     * Add an answer that throws an exception.
     *
     * @param Exception|null $exception                The exception, or null to throw a generic exception.
     * @param Exception      $additionalExceptions,... Additional exceptions for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function throws(Exception $exception = null)
    {
        if (0 === func_num_args()) {
            return $this->does(
                function () {
                    throw new Exception();
                }
            );
        }

        foreach (func_get_args() as $exception) {
            $this->does(
                function () use ($exception) {
                    throw $exception;
                }
            );
        }

        return $this;
    }

    /**
     * Invoke the stub.
     *
     * This method supports reference parameters.
     *
     * @param array<integer,mixed>|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the stub throws an exception.
     */
    public function invokeWith(array $arguments = null)
    {
        if (null === $arguments) {
            $arguments = array();
        }

        if ($this->isNewRule) {
            $this->returns();
        }

        foreach ($this->rules as $ruleIndex => &$rule) {
            if ($this->matcherVerifier->matches($rule[0], $arguments)) {
                $this->ruleCounts[$ruleIndex]++;

                if ($callbacks = current($rule[1])) {
                    next($rule[1]);
                } else {
                    $callbacks = end($rule[1]);
                }

                foreach ($callbacks[1] as $callback) {
                    if ($callback[2]) {
                        $callbackArguments =
                            array_merge($callback[1], $arguments);
                    } else {
                        $callbackArguments = $callback[1];
                    }

                    call_user_func_array($callback[0], $callbackArguments);
                }

                return call_user_func_array($callbacks[0], $arguments);
            }
        }
    } // @codeCoverageIgnore

    /**
     * Invoke the stub.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the stub throws an exception.
     */
    public function invoke()
    {
        return $this->invokeWith(func_get_args());
    }

    /**
     * Invoke the stub.
     *
     * @param mixed $arguments,...
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the stub throws an exception.
     */
    public function __invoke()
    {
        return $this->invokeWith(func_get_args());
    }

    private $matcherFactory;
    private $matcherVerifier;
    private $isNewRule;
    private $rules;
    private $ruleCounts;
}
