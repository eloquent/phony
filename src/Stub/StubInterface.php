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

use Exception;

/**
 * The interface implemented by stubs.
 */
interface StubInterface
{
    /**
     * Set the $this value of this stub.
     *
     * This value is used by returnsThis().
     *
     * @param object $thisValue The $this value.
     */
    public function setThisValue($thisValue);

    /**
     * Get the $this value of this stub.
     *
     * @return object|null The $this value, or null to use the stub object.
     */
    public function thisValue();

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
     * Add an answer that returns the $this value.
     *
     * @return StubInterface This stub.
     */
    public function returnsThis();

    /**
     * Add an answer that throws an exception.
     *
     * @param Exception|null $exception                The exception, or null to throw a generic exception.
     * @param Exception      $additionalExceptions,... Additional exceptions for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function throws(Exception $exception = null);

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
    public function invokeWith(array $arguments = null);

    /**
     * Invoke the stub.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the stub throws an exception.
     */
    public function invoke();

    /**
     * Invoke the stub.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the stub throws an exception.
     */
    public function __invoke();
}
