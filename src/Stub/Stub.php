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

use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
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
class Stub extends AbstractWrappedInvocable implements StubInterface
{
    /**
     * Construct a new stub.
     *
     * @param callable|null                 $callback        The callback, or null to create an unbound stub.
     * @param mixed                         $self            The self value.
     * @param integer|null                  $id              The identifier.
     * @param MatcherFactoryInterface|null  $matcherFactory  The matcher factory to use.
     * @param MatcherVerifierInterface|null $matcherVerifier The matcher verifier to use.
     * @param InvokerInterface|null         $invoker         The invoker to use.
     */
    public function __construct(
        $callback = null,
        $self = null,
        $id = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        InvokerInterface $invoker = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }

        parent::__construct($callback, $id);

        if (null === $self) {
            $self = $this->callback;
        }

        $this->self = $self;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->invoker = $invoker;
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
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
    }

    /**
     * Set the self value of this stub.
     *
     * This value is used by returnsThis().
     *
     * @param object $self The self value.
     */
    public function setSelf($self)
    {
        if ($self === $this) {
            $self = null;
        }

        $this->self = $self;
    }

    /**
     * Get the self value of this stub.
     *
     * @return object The self value.
     */
    public function self()
    {
        if ($this->self) {
            return $this->self;
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

        $this->callbacks[] = array(
            $this->returnsCallbackCallback($callback),
            $arguments,
            true,
            $appendArguments,
            false,
        );

        return $this;
    }

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative indices are equivalent to $argumentCount - $index.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param integer|null $index         The argument index, or null to call the first argument.
     * @param mixed        $arguments,... The arguments to call the callback with.
     *
     * @return StubInterface This stub.
     */
    public function callsArgument($index = null)
    {
        if (0 === func_num_args()) {
            $arguments = array();
        } else {
            $arguments = func_get_args();
            array_shift($arguments);
        }

        return $this->callsArgumentWith($index, $arguments, false);
    }

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative indices are equivalent to $argumentCount - $index.
     *
     * This method supports reference parameters in the supplied arguments, but
     * not in the invocation arguments.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param integer|null              $index           The argument index, or null to call the first argument.
     * @param array<integer,mixed>|null $arguments       The arguments to call the callback with.
     * @param boolean|null              $appendArguments True if the invocation arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function callsArgumentWith(
        $index = null,
        array $arguments = null,
        $appendArguments = null
    ) {
        if (null === $arguments) {
            $arguments = array();
        }
        if (null === $appendArguments) {
            $appendArguments = false;
        }

        $this->callbacks[] = array(
            $this->returnsArgumentCallback($index),
            $arguments,
            true,
            $appendArguments,
            false,
        );

        return $this;
    }

    /**
     * Set the value of an argument passed by reference as part of an answer.
     *
     * @param mixed        $value The value to set the argument to.
     * @param integer|null $index The argument index, or null to set the first argument.
     *
     * @return StubInterface This stub.
     */
    public function setsArgument($value, $index = null)
    {
        if (null === $index) {
            $index = 0;
        }

        $this->callbacks[] = array(
            $this->returnsCallbackCallback(
                function ($self, array $arguments) use ($value, $index) {
                    $argumentCount = count($arguments);

                    if ($argumentCount < 1) {
                        return;
                    }

                    if ($index < 0) {
                        $index = $argumentCount + $index;
                    }

                    if ($index >= $argumentCount) {
                        return;
                    }

                    $arguments[$index] = $value;
                }
            ),
            array(),
            true,
            false,
            true,
        );

        return $this;
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable $callback      The callback.
     * @param mixed    $arguments,... The arguments to call the callback with.
     *
     * @return StubInterface This stub.
     */
    public function does($callback)
    {
        $arguments = func_get_args();
        array_shift($arguments);

        if ($this->isNewRule) {
            $this->isNewRule = false;

            array_unshift($this->rules, array($this->matchers, array()));
            array_unshift($this->ruleCounts, 0);
        }

        $this->rules[0][1][] = array(
            $callback,
            $arguments,
            true,
            true,
            false,
            $this->callbacks
        );
        $this->callbacks = array();

        return $this;
    }

    /**
     * Add an answer that calls the wrapped callback.
     *
     * @return StubInterface This stub.
     */
    public function forwards()
    {
        $invoker = $this->invoker;
        $callback = $this->callback;

        if ($this->isNewRule) {
            $this->isNewRule = false;

            array_unshift($this->rules, array($this->matchers, array()));
            array_unshift($this->ruleCounts, 0);
        }

        $this->rules[0][1][] = array(
            function (array $arguments) use ($invoker, $callback) {
                return $invoker->callWith($callback, $arguments);
            },
            array(),
            false,
            false,
            true,
            $this->callbacks
        );
        $this->callbacks = array();

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
        return $this->does($this->returnsArgumentCallback($index));
    }

    /**
     * Add an answer that returns the self value.
     *
     * @return StubInterface This stub.
     */
    public function returnsSelf()
    {
        return $this->does(
            function ($self) {
                return $self;
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
        if (null === $arguments) {
            $arguments = array();
        }

        if ($this->isNewRule) {
            $this->returns();
        }

        /*

        structure is as follows:

        (rules) [
            (rule) [
                (criteria) [matcher, ...]
                (answers) [
                    (answer) [
                        (primary callback) callable
                        (arguments) [mixed, ...]
                        (prefix self) boolean
                        (append arguments) boolean
                        (append argument array) boolean
                        (secondary callbacks) [
                            (call details) [
                                (callback callback) callable
                                (arguments) [mixed, ...]
                                (prefix self) boolean
                                (append arguments) boolean
                                (append argument array) boolean
                            ]
                        ]
                    ]
                ]
            ]
        ]

        */

        foreach ($this->rules as $ruleIndex => &$rule) {
            if ($this->matcherVerifier->matches($rule[0], $arguments)) {
                break;
            }
        }

        $this->ruleCounts[$ruleIndex]++;

        // pull out the current answer, using the last one once they are
        // exhausted
        if ($answer = current($rule[1])) {
            next($rule[1]);
        } else {
            $answer = end($rule[1]);
        }

        // invoke callbacks added via calls() and friends
        foreach ($answer[5] as $callDetails) {
            // get the actual callback, because it could be an argument
            $argumentsWithSelf = $arguments;
            array_unshift($argumentsWithSelf, $this->self);
            $callback =
                $this->invoker->callWith($callDetails[0], $argumentsWithSelf);

            // only call the callback if it's sane to do so
            if (is_callable($callback)) {
                $callbackArguments = $callDetails[1];

                if ($callDetails[2]) {
                    array_unshift($callbackArguments, $this->self);
                }

                if ($callDetails[3]) {
                    $callbackArguments =
                        array_merge($callbackArguments, $arguments);
                }

                if ($callDetails[4]) {
                    $callbackArguments[] = $arguments;
                }

                // invoke secondary callback
                $this->invoker->callWith($callback, $callbackArguments);
            }
        }

        $doesArguments = $answer[1];

        if ($answer[2]) {
            array_unshift($doesArguments, $this->self);
        }

        if ($answer[3]) {
            $doesArguments = array_merge($doesArguments, $arguments);
        }

        if ($answer[4]) {
            $doesArguments[] = $arguments;
        }

        // invoke primary callback
        return $this->invoker->callWith($answer[0], $doesArguments);
    }

    /**
     * Returns a callback that returns the supplied callback.
     *
     * @param callable $callback The callback to return from the callback.
     *
     * @return callable The callback.
     */
    protected function returnsCallbackCallback($callback)
    {
        return function () use ($callback) {
            return $callback;
        };
    }

    /**
     * Returns a callback that returns the argument at $index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return callable The callback.
     */
    protected function returnsArgumentCallback($index = null)
    {
        if (null === $index) {
            $index = 0;
        }

        return function () use ($index) {
            $argumentCount = func_num_args() - 1;

            if ($argumentCount < 1) {
                return;
            }

            if ($index < 0) {
                $index = $argumentCount + $index;
            }

            if ($index >= $argumentCount) {
                return;
            }

            return func_get_arg($index + 1);
        };
    }

    private $self;
    private $matcherFactory;
    private $matcherVerifier;
    private $invoker;
    private $matchers;
    private $callbacks;
    private $isNewRule;
    private $rules;
    private $ruleCounts;
}
