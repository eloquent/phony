<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Invocation\WrappedInvocableInterface;
use Error;
use Exception;

/**
 * The interface implemented by stubs.
 *
 * @api
 */
interface StubInterface extends WrappedInvocableInterface
{
    /**
     * Set the self value of this stub.
     *
     * This value is used by returnsSelf().
     *
     * @api
     *
     * @param mixed $self The self value.
     *
     * @return $this This stub.
     */
    public function setSelf($self);

    /**
     * Get the self value of this stub.
     *
     * @api
     *
     * @return mixed The self value.
     */
    public function self();

    /**
     * Set the callback to use when creating a default answer.
     *
     * @api
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This stub.
     */
    public function setDefaultAnswerCallback($defaultAnswerCallback);

    /**
     * Get the default answer callback.
     *
     * @api
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback();

    /**
     * Modify the current criteria to match the supplied arguments.
     *
     * @api
     *
     * @param mixed ...$argument The arguments.
     *
     * @return $this This stub.
     */
    public function with();

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @api
     *
     * @param callable $callback The callback.
     * @param callable ...$additionalCallbacks Additional callbacks.
     *
     * @return $this This stub.
     */
    public function calls($callback);

    /**
     * Add a callback to be called as part of an answer.
     *
     * This method supports reference parameters.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @api
     *
     * @param callable                 $callback             The callback.
     * @param ArgumentsInterface|array $arguments            The arguments.
     * @param boolean|null             $prefixSelf           True if the self value should be prefixed.
     * @param boolean                  $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean                  $suffixArguments      True if arguments should be appended.
     */
    public function callsWith(
        $callback,
        $arguments = array(),
        $prefixSelf = null,
        $suffixArgumentsArray = false,
        $suffixArguments = true
    );

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @api
     *
     * @param integer $index The argument index.
     * @param integer ...$additionalIndices Additional argument indices to call.
     *
     * @return $this This stub.
     */
    public function callsArgument($index = 0);

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * This method supports reference parameters in the supplied arguments, but
     * not in the invocation arguments.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @api
     *
     * @param integer                  $index                The argument index.
     * @param ArgumentsInterface|array $arguments            The arguments.
     * @param boolean|null             $prefixSelf           True if the self value should be prefixed.
     * @param boolean                  $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean                  $suffixArguments      True if arguments should be appended.
     *
     * @return $this This stub.
     */
    public function callsArgumentWith(
        $index = 0,
        $arguments = array(),
        $prefixSelf = null,
        $suffixArgumentsArray = false,
        $suffixArguments = true
    );

    /**
     * Set the value of an argument passed by reference as part of an answer.
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
     * @return $this This stub.
     */
    public function setsArgument($indexOrValue = null, $value = null);

    /**
     * Add a callback as an answer.
     *
     * @api
     *
     * @param callable $callback The callback.
     * @param callable ...$additionalCallbacks Additional callbacks for subsequent invocations.
     *
     * @return $this This stub.
     */
    public function does($callback);

    /**
     * Add a callback as an answer.
     *
     * @api
     *
     * @param callable                 $callback             The callback.
     * @param ArgumentsInterface|array $arguments            The arguments.
     * @param boolean|null             $prefixSelf           True if the self value should be prefixed.
     * @param boolean                  $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean                  $suffixArguments      True if arguments should be appended.
     *
     * @return $this This stub.
     */
    public function doesWith(
        $callback,
        $arguments = array(),
        $prefixSelf = null,
        $suffixArgumentsArray = false,
        $suffixArguments = true
    );

    /**
     * Add an answer that calls the wrapped callback.
     *
     * @api
     *
     * @param ArgumentsInterface|array $arguments            The arguments.
     * @param boolean|null             $prefixSelf           True if the self value should be prefixed.
     * @param boolean                  $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean                  $suffixArguments      True if arguments should be appended.
     *
     * @return $this This stub.
     */
    public function forwards(
        $arguments = array(),
        $prefixSelf = null,
        $suffixArgumentsArray = false,
        $suffixArguments = true
    );

    /**
     * Add an answer that returns a value.
     *
     * @api
     *
     * @param mixed $value The return value.
     * @param mixed ...$additionalValues Additional return values for subsequent invocations.
     *
     * @return $this This stub.
     */
    public function returns($value = null);

    /**
     * Add an answer that returns an argument.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer $index The argument index.
     *
     * @return $this This stub.
     */
    public function returnsArgument($index = 0);

    /**
     * Add an answer that returns the self value.
     *
     * @api
     *
     * @return $this This stub.
     */
    public function returnsSelf();

    /**
     * Add an answer that throws an exception.
     *
     * @api
     *
     * @param Exception|Error|string|null $exception The exception, or message, or null to throw a generic exception.
     * @param Exception|Error|string      ...$additionalExceptions Additional exceptions, or messages, for subsequent invocations.
     *
     * @return $this This stub.
     */
    public function throws($exception = null);

    /**
     * Close any existing rule.
     *
     * @return $this This stub.
     */
    public function closeRule();
}
