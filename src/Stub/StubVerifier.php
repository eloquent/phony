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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Spy\SpyVerifier;
use Exception;

/**
 * Pairs a stub and a spy, and provides convenience methods for verifying
 * interactions with the spy.
 *
 * @internal
 */
class StubVerifier extends SpyVerifier implements StubVerifierInterface
{
    /**
     * Construct a new stub verifier.
     *
     * @param StubInterface|null                $stub                The stub.
     * @param SpyInterface|null                 $spy                 The spy.
     * @param MatcherFactoryInterface|null      $matcherFactory      The matcher factory to use.
     * @param MatcherVerifierInterface|null     $matcherVerifier     The macther verifier to use.
     * @param CallVerifierFactoryInterface|null $callVerifierFactory The call verifier factory to use.
     * @param AssertionRecorderInterface|null   $assertionRecorder   The assertion recorder to use.
     * @param AssertionRendererInterface|null   $assertionRenderer   The assertion renderer to use.
     * @param InvokerInterface|null             $invoker             The invoker to use.
     */
    public function __construct(
        StubInterface $stub = null,
        SpyInterface $spy = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        CallVerifierFactoryInterface $callVerifierFactory = null,
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null,
        InvokerInterface $invoker = null
    ) {
        if (null === $stub) {
            $stub = new Stub();
        }
        if (null === $spy) {
            $spy = new Spy($stub);
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }

        parent::__construct(
            $spy,
            $matcherFactory,
            $matcherVerifier,
            $callVerifierFactory,
            $assertionRecorder,
            $assertionRenderer
        );

        $this->stub = $stub;
        $this->invoker = $invoker;
    }

    /**
     * Get the stub.
     *
     * @return StubInterface The stub.
     */
    public function stub()
    {
        return $this->stub;
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
        $this->stub->setSelf($self);
    }

    /**
     * Get the self value of this stub.
     *
     * @return object The self value.
     */
    public function self()
    {
        return $this->stub->self();
    }

    /**
     * Get the identifier.
     *
     * @return integer|null The identifier.
     */
    public function id()
    {
        return $this->stub->id();
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
        $this->invoker
            ->callWith(array($this->stub, __FUNCTION__), func_get_args());

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
        $this->invoker
            ->callWith(array($this->stub, __FUNCTION__), func_get_args());

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable $callback                The callback.
     * @param callable $additionalCallbacks,... Additional callbacks.
     *
     * @return StubInterface This stub.
     */
    public function calls($callback)
    {
        $this->invoker
            ->callWith(array($this->stub, __FUNCTION__), func_get_args());

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * This method supports reference parameters.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable                  $callback             The callback.
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     */
    public function callsWith(
        $callback,
        array $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        $this->stub->callsWith(
            $callback,
            $arguments,
            $prefixSelf,
            $suffixArgumentsArray,
            $suffixArguments
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
     * @param integer|null $index                 The argument index, or null to call the first argument.
     * @param integer|null $additionalIndices,... Additional argument indices to call.
     *
     * @return StubInterface This stub.
     */
    public function callsArgument($index = null)
    {
        $this->invoker
            ->callWith(array($this->stub, __FUNCTION__), func_get_args());

        return $this;
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
     * @param integer|null              $index                The argument index, or null to call the first argument.
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function callsArgumentWith(
        $index = null,
        array $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        $this->stub->callsArgumentWith(
            $index,
            $arguments,
            $prefixSelf,
            $suffixArgumentsArray,
            $suffixArguments
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
        $this->stub->setsArgument($value, $index);

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
        $this->invoker
            ->callWith(array($this->stub, __FUNCTION__), func_get_args());

        return $this;
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable                  $callback             The callback.
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function doesWith(
        $callback,
        array $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        $this->stub->doesWith(
            $callback,
            $arguments,
            $prefixSelf,
            $suffixArgumentsArray,
            $suffixArguments
        );

        return $this;
    }

    /**
     * Add an answer that calls the wrapped callback.
     *
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function forwards(
        array $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        $this->stub->forwards(
            $arguments,
            $prefixSelf,
            $suffixArgumentsArray,
            $suffixArguments
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
        $this->invoker
            ->callWith(array($this->stub, __FUNCTION__), func_get_args());

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
        $this->stub->returnsArgument($index);

        return $this;
    }

    /**
     * Add an answer that returns the self value.
     *
     * @return StubInterface This stub.
     */
    public function returnsSelf()
    {
        $this->stub->returnsSelf();

        return $this;
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
        $this->invoker
            ->callWith(array($this->stub, __FUNCTION__), func_get_args());

        return $this;
    }

    private $stub;
    private $invoker;
}
