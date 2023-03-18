<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Invocation\WrappedInvocable;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilder;
use Throwable;

/**
 * The interface implemented by stubs.
 */
interface Stub extends WrappedInvocable
{
    /**
     * Set the self value of this stub.
     *
     * This value is used by returnsSelf().
     *
     * @param mixed $self The self value.
     *
     * @return $this This stub.
     */
    public function setSelf($self): self;

    /**
     * Get the self value of this stub.
     *
     * @return mixed The self value.
     */
    public function self();

    /**
     * Set the callback to use when creating a default answer.
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This stub.
     */
    public function setDefaultAnswerCallback(
        callable $defaultAnswerCallback
    ): self;

    /**
     * Get the default answer callback.
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback(): callable;

    /**
     * Modify the current criteria to match the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return $this This stub.
     */
    public function with(...$arguments): self;

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable ...$callbacks The callbacks.
     *
     * @return $this This stub.
     */
    public function calls(callable ...$callbacks): self;

    /**
     * Add a callback to be called as part of an answer.
     *
     * This method supports reference parameters.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable                          $callback              The callback.
     * @param Arguments|array<int|string,mixed> $arguments             The arguments.
     * @param ?bool                             $prefixSelf            True if the self value should be prefixed.
     * @param bool                              $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                              $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This stub.
     */
    public function callsWith(
        callable $callback,
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): self;

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `0`.
     *
     * Negative positions are offset from the end of the positional arguments.
     * That is, `-1` indicates the last positional argument, and `-2` indicates
     * the second-to-last positional argument.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param int|string ...$positionsOrNames The argument positions and/or names.
     *
     * @return $this                      This stub.
     * @throws UndefinedArgumentException If a requested argument is undefined.
     */
    public function callsArgument(int|string ...$positionsOrNames): self;

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative positions are offset from the end of the positional arguments.
     * That is, `-1` indicates the last positional argument, and `-2` indicates
     * the second-to-last positional argument.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param int|string                        $positionOrName        The argument position or name.
     * @param Arguments|array<int|string,mixed> $arguments             The arguments.
     * @param bool                              $prefixSelf            True if the self value should be prefixed.
     * @param bool                              $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                              $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this                      This stub.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function callsArgumentWith(
        int|string $positionOrName = 0,
        $arguments = [],
        bool $prefixSelf = false,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): self;

    /**
     * Set the value of an argument passed by reference as part of an answer.
     *
     * If called with no arguments, sets the first argument to null.
     *
     * If called with one argument, sets the first argument to
     * `$positionOrNameOrValue`.
     *
     * If called with two arguments, sets the argument at
     * `$positionOrNameOrValue` to `$value`.
     *
     * Negative positions are offset from the end of the positional arguments.
     * That is, `-1` indicates the last positional argument, and `-2` indicates
     * the second-to-last positional argument.
     *
     * @param mixed $positionOrNameOrValue The position, or name; or value, if no position or name is specified.
     * @param mixed $value                 The value.
     *
     * @return $this                      This stub.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function setsArgument($positionOrNameOrValue = null, $value = null): self;

    /**
     * Add a callback as an answer.
     *
     * @param callable ...$callbacks The callbacks.
     *
     * @return $this This stub.
     */
    public function does(callable ...$callbacks): self;

    /**
     * Add a callback as an answer.
     *
     * @param callable                          $callback              The callback.
     * @param Arguments|array<int|string,mixed> $arguments             The arguments.
     * @param ?bool                             $prefixSelf            True if the self value should be prefixed.
     * @param bool                              $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                              $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This stub.
     */
    public function doesWith(
        callable $callback,
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): self;

    /**
     * Add an answer that calls the wrapped callback.
     *
     * @param Arguments|array<int|string,mixed> $arguments             The arguments.
     * @param ?bool                             $prefixSelf            True if the self value should be prefixed.
     * @param bool                              $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                              $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This stub.
     */
    public function forwards(
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): self;

    /**
     * Add an answer that returns a value.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `null`.
     *
     * @param mixed ...$values The return values.
     *
     * @return $this This stub.
     */
    public function returns(...$values): self;

    /**
     * Add an answer that returns an argument.
     *
     * Negative positions are offset from the end of the positional arguments.
     * That is, `-1` indicates the last positional argument, and `-2` indicates
     * the second-to-last positional argument.
     *
     * @param int|string $positionOrName The argument position or name.
     *
     * @return $this                      This stub.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function returnsArgument(int|string $positionOrName = 0): self;

    /**
     * Add an answer that returns the self value.
     *
     * @return $this This stub.
     */
    public function returnsSelf(): self;

    /**
     * Add an answer that throws an exception.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `null`.
     *
     * @param Throwable|string|null ...$exceptions The exceptions, or messages, or nulls to throw generic exceptions.
     *
     * @return $this This stub.
     */
    public function throws(...$exceptions): self;

    /**
     * Add an answer that returns a generator, and return a builder for
     * customizing the generator's behavior.
     *
     * @param iterable<mixed> ...$values Sets of keys and values to yield.
     *
     * @return GeneratorAnswerBuilder The answer builder.
     */
    public function generates(iterable ...$values): GeneratorAnswerBuilder;

    /**
     * Close any existing rule.
     *
     * @return $this This stub.
     */
    public function closeRule(): self;
}
