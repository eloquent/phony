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

use Eloquent\Phony\Invocation\WrappedInvocableInterface;
use Exception;

/**
 * The interface implemented by stubs.
 */
interface StubInterface extends WrappedInvocableInterface
{
    /**
     * Set the self value of this stub.
     *
     * This value is used by returnsSelf().
     *
     * @param object $self The self value.
     */
    public function setSelf($self);

    /**
     * Get the self value of this stub.
     *
     * @return object The self value.
     */
    public function self();

    /**
     * Modify the current criteria to match the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function with();

    /**
     * Modify the current criteria to match the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function withExactly();

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
    public function calls($callback);

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
    );

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
    public function callsArgument($index = null);

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
    );

    /**
     * Set the value of an argument passed by reference as part of an answer.
     *
     * @param mixed        $value The value to set the argument to.
     * @param integer|null $index The argument index, or null to set the first argument.
     *
     * @return StubInterface This stub.
     */
    public function setsArgument($value, $index = null);

    /**
     * Add a callback as an answer.
     *
     * @param callable $callback                The callback.
     * @param callable $additionalCallbacks,... Additional callbacks for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function does($callback);

    /**
     * Add an answer that calls the wrapped callback.
     *
     * @return StubInterface This stub.
     */
    public function forwards();

    /**
     * Add an answer that returns a value.
     *
     * @param mixed $value                The return value.
     * @param mixed $additionalValues,... Additional return values for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function returns($value = null);

    /**
     * Add an answer that returns an argument.
     *
     * Negative indices are equivalent to $argumentCount - $index.
     *
     * @param integer|null $index The argument index, or null to return the first argument.
     *
     * @return StubInterface This stub.
     */
    public function returnsArgument($index = null);

    /**
     * Add an answer that returns the self value.
     *
     * @return StubInterface This stub.
     */
    public function returnsSelf();

    /**
     * Add an answer that throws an exception.
     *
     * @param Exception|null $exception                The exception, or null to throw a generic exception.
     * @param Exception      $additionalExceptions,... Additional exceptions for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function throws(Exception $exception = null);
}
