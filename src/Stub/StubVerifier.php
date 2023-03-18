<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Invocation\WrappedInvocable;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilder;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use Throwable;

/**
 * Pairs a stub and a spy, and provides convenience methods for verifying
 * interactions with the spy.
 */
class StubVerifier extends SpyVerifier implements Stub
{
    /**
     * Construct a new stub verifier.
     *
     * @param Stub                          $stub                          The stub.
     * @param Spy                           $spy                           The spy.
     * @param MatcherFactory                $matcherFactory                The matcher factory to use.
     * @param MatcherVerifier               $matcherVerifier               The macther verifier to use.
     * @param GeneratorVerifierFactory      $generatorVerifierFactory      The generator verifier factory to use.
     * @param IterableVerifierFactory       $iterableVerifierFactory       The iterable verifier factory to use.
     * @param CallVerifierFactory           $callVerifierFactory           The call verifier factory to use.
     * @param AssertionRecorder             $assertionRecorder             The assertion recorder to use.
     * @param AssertionRenderer             $assertionRenderer             The assertion renderer to use.
     * @param GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory The generator answer builder factory to use.
     */
    public function __construct(
        Stub $stub,
        Spy $spy,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        IterableVerifierFactory $iterableVerifierFactory,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory
    ) {
        parent::__construct(
            $spy,
            $matcherFactory,
            $matcherVerifier,
            $generatorVerifierFactory,
            $iterableVerifierFactory,
            $callVerifierFactory,
            $assertionRecorder,
            $assertionRenderer
        );

        $this->stub = $stub;
        $this->generatorAnswerBuilderFactory = $generatorAnswerBuilderFactory;
    }

    /**
     * Get the stub.
     *
     * @return Stub The stub.
     */
    public function stub(): Stub
    {
        return $this->stub;
    }

    /**
     * Set the self value of this stub.
     *
     * This value is used by returnsThis().
     *
     * @param mixed $self The self value.
     *
     * @return $this This stub.
     */
    public function setSelf($self): Stub
    {
        $this->stub->setSelf($self);

        return $this;
    }

    /**
     * Get the self value of this stub.
     *
     * @return mixed The self value.
     */
    public function self()
    {
        return $this->stub->self();
    }

    /**
     * Get the default answer callback.
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback(): callable
    {
        return $this->stub->defaultAnswerCallback();
    }

    /**
     * Set the callback to use when creating a default answer.
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This stub.
     */
    public function setDefaultAnswerCallback(
        callable $defaultAnswerCallback
    ): Stub {
        $this->stub->setDefaultAnswerCallback($defaultAnswerCallback);

        return $this;
    }

    /**
     * Set the label.
     *
     * @param string $label The label.
     *
     * @return $this This invocable.
     */
    public function setLabel(string $label): WrappedInvocable
    {
        $this->stub->setLabel($label);

        return $this;
    }

    /**
     * Get the label.
     *
     * @return string The label.
     */
    public function label(): string
    {
        return $this->stub->label();
    }

    /**
     * Modify the current criteria to match the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return $this This stub.
     */
    public function with(...$arguments): Stub
    {
        $this->stub->with(...$arguments);

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable ...$callbacks The callbacks.
     *
     * @return $this This stub.
     */
    public function calls(callable ...$callbacks): Stub
    {
        $this->stub->calls(...$callbacks);

        return $this;
    }

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
     */
    public function callsWith(
        callable $callback,
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): Stub {
        $this->stub->callsWith(
            $callback,
            $arguments,
            $prefixSelf,
            $suffixArgumentsObject,
            $suffixArguments
        );

        return $this;
    }

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
    public function callsArgument(int|string ...$positionsOrNames): Stub
    {
        $this->stub->callsArgument(...$positionsOrNames);

        return $this;
    }

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
    ): Stub {
        $this->stub->callsArgumentWith(
            $positionOrName,
            $arguments,
            $prefixSelf,
            $suffixArgumentsObject,
            $suffixArguments
        );

        return $this;
    }

    /**
     * Set the value of an argument passed by reference as part of an answer.
     *
     * If called with no arguments, sets the first positional argument to null.
     *
     * If called with one argument, sets the first positional argument to
     * `$positionOrNameOrValue`.
     *
     * If called with two arguments, sets the argument at
     * `$positionOrNameOrValue` to `$value`.
     *
     * @param mixed $positionOrNameOrValue The position, or name; or value, if no position or name is specified.
     * @param mixed $value                 The value.
     *
     * @return $this                      This stub.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function setsArgument(
        $positionOrNameOrValue = null,
        $value = null
    ): Stub {
        if (func_num_args() > 1) {
            $this->stub->setsArgument($positionOrNameOrValue, $value);
        } else {
            $this->stub->setsArgument($positionOrNameOrValue);
        }

        return $this;
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable ...$callbacks The callbacks.
     *
     * @return $this This stub.
     */
    public function does(callable ...$callbacks): Stub
    {
        $this->stub->does(...$callbacks);

        return $this;
    }

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
    ): Stub {
        $this->stub->doesWith(
            $callback,
            $arguments,
            $prefixSelf,
            $suffixArgumentsObject,
            $suffixArguments
        );

        return $this;
    }

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
    ): Stub {
        $this->stub->forwards(
            $arguments,
            $prefixSelf,
            $suffixArgumentsObject,
            $suffixArguments
        );

        return $this;
    }

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
    public function returns(...$values): Stub
    {
        $this->stub->returns(...$values);

        return $this;
    }

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
    public function returnsArgument(int|string $positionOrName = 0): Stub
    {
        $this->stub->returnsArgument($positionOrName);

        return $this;
    }

    /**
     * Add an answer that returns the self value.
     *
     * @return $this This stub.
     */
    public function returnsSelf(): Stub
    {
        $this->stub->returnsSelf();

        return $this;
    }

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
    public function throws(...$exceptions): Stub
    {
        $this->stub->throws(...$exceptions);

        return $this;
    }

    /**
     * Add an answer that returns a generator, and return a builder for
     * customizing the generator's behavior.
     *
     * @param iterable<mixed> ...$values Sets of keys and values to yield.
     *
     * @return GeneratorAnswerBuilder The answer builder.
     */
    public function generates(iterable ...$values): GeneratorAnswerBuilder
    {
        $builder = $this->generatorAnswerBuilderFactory->create($this);
        $this->stub->doesWith($builder->answer(), [], true, true, false);

        foreach ($values as $index => $subValues) {
            if ($index > 0) {
                $builder->returns();

                $builder = $this->generatorAnswerBuilderFactory->create($this);
                $this->stub
                    ->doesWith($builder->answer(), [], true, true, false);
            }

            $builder->yieldsFrom($subValues);
        }

        return $builder;
    }

    /**
     * Close any existing rule.
     *
     * @return $this This stub.
     */
    public function closeRule(): Stub
    {
        $this->stub->closeRule();

        return $this;
    }

    /**
     * Limits the output displayed when `var_dump` is used.
     *
     * @return array<string,mixed> The contents to export.
     */
    public function __debugInfo(): array
    {
        return ['stub' => $this->stub];
    }

    /**
     * @var Stub
     */
    private $stub;

    /**
     * @var GeneratorAnswerBuilderFactory
     */
    private $generatorAnswerBuilderFactory;
}
