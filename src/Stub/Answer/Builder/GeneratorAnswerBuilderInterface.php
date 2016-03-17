<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer\Builder;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;

/**
 * The interface implemented by generator answer builders.
 *
 * @api
 */
interface GeneratorAnswerBuilderInterface
{
    /**
     * Add a callback to be called as part of the answer.
     *
     * @api
     *
     * @param callable $callback The callback.
     * @param callable ...$additionalCallbacks Additional callbacks.
     *
     * @return $this This builder.
     */
    public function calls($callback);

    /**
     * Add a callback to be called as part of the answer.
     *
     * @api
     *
     * @param callable                 $callback              The callback.
     * @param ArgumentsInterface|array $arguments             The arguments.
     * @param boolean|null             $prefixSelf            True if the self value should be prefixed.
     * @param boolean                  $suffixArgumentsObject True if the arguments object should be appended.
     * @param boolean                  $suffixArguments       True if the arguments should be appended individually.
     */
    public function callsWith(
        $callback,
        $arguments = array(),
        $prefixSelf = null,
        $suffixArgumentsObject = false,
        $suffixArguments = true
    );

    /**
     * Add an argument callback to be called as part of the answer.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer $index The argument index.
     * @param integer ...$additionalIndices Additional argument indices to call.
     *
     * @return $this This builder.
     */
    public function callsArgument($index = 0);

    /**
     * Add an argument callback to be called as part of the answer.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer                  $index                 The argument index.
     * @param ArgumentsInterface|array $arguments             The arguments.
     * @param boolean|null             $prefixSelf            True if the self value should be prefixed.
     * @param boolean                  $suffixArgumentsObject True if the arguments object should be appended.
     * @param boolean                  $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This builder.
     */
    public function callsArgumentWith(
        $index = 0,
        $arguments = array(),
        $prefixSelf = null,
        $suffixArgumentsObject = false,
        $suffixArguments = true
    );

    /**
     * Set the value of an argument passed by reference as part of the answer.
     *
     * If called with no arguments, sets the first argument to null.
     *
     * If called with one argument, sets the first argument to $indexOrValue.
     *
     * If called with two arguments, sets the argument at $indexOrValue to
     * $value.
     *
     * @api
     *
     * @param mixed $indexOrValue The index, or value if no index is specified.
     * @param mixed $value        The value.
     *
     * @return $this This builder.
     */
    public function setsArgument($indexOrValue = null, $value = null);

    /**
     * Add a yielded value to the answer.
     *
     * If both `$keyOrValue` and `$value` are supplied, the stub will yield like
     * `yield $keyOrValue => $value;`.
     *
     * If only `$keyOrValue` is supplied, the stub will yield like
     * `yield $keyOrValue;`.
     *
     * If no arguments are supplied, the stub will yield like `yield;`.
     *
     * @api
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return $this This builder.
     */
    public function yields($keyOrValue = null, $value = null);

    /**
     * Add a set of yielded values to the answer.
     *
     * @api
     *
     * @param mixed<mixed,mixed> $values The set of keys and values to yield.
     *
     * @return $this This builder.
     */
    public function yieldsFrom($values);

    /**
     * End the generator by returning a value.
     *
     * @api
     *
     * @param mixed $value The return value.
     * @param mixed ...$additionalValues Additional return values for subsequent invocations.
     *
     * @return StubInterface    The stub.
     * @throws RuntimeException If the current runtime does not support the supplied return value.
     */
    public function returns($value = null);

    /**
     * End the generator by returning an argument.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer $index The argument index.
     *
     * @return StubInterface The stub.
     */
    public function returnsArgument($index = 0);

    /**
     * End the generator by returning the self value.
     *
     * @api
     *
     * @return StubInterface The stub.
     */
    public function returnsSelf();

    /**
     * End the generator by throwing an exception.
     *
     * @api
     *
     * @param Exception|Error|string|null $exception The exception, or message, or null to throw a generic exception.
     * @param Exception|Error|string      ...$additionalExceptions Additional exceptions, or messages, for subsequent invocations.
     *
     * @return StubInterface The stub.
     */
    public function throws($exception = null);

    /**
     * Get the answer.
     *
     * @return callable The answer.
     */
    public function answer();
}
