<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer\Builder;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Stub\Answer\CallRequest;
use Eloquent\Phony\Stub\Stub;
use Exception;
use Throwable;

/**
 * Builds generator stub answers.
 */
class GeneratorAnswerBuilder
{
    /**
     * Construct a new generator answer builder.
     *
     * @param Stub               $stub               The stub.
     * @param InvocableInspector $invocableInspector The invocable inspector to use.
     * @param Invoker            $invoker            The invoker to use.
     */
    public function __construct(
        Stub $stub,
        InvocableInspector $invocableInspector,
        Invoker $invoker
    ) {
        $this->stub = $stub;
        $this->invocableInspector = $invocableInspector;
        $this->invoker = $invoker;

        $this->requests = [];
        $this->iterations = [];
        $this->returnsSelf = false;
    }

    /**
     * Add a callback to be called as part of the answer.
     *
     * @param callable ...$callbacks The callbacks.
     *
     * @return $this This builder.
     */
    public function calls(callable ...$callbacks): self
    {
        foreach ($callbacks as $callback) {
            $this->callsWith($callback);
        }

        return $this;
    }

    /**
     * Add a callback to be called as part of the answer.
     *
     * This method supports reference parameters.
     *
     * @param callable                   $callback              The callback.
     * @param Arguments|array<int,mixed> $arguments             The arguments.
     * @param ?bool                      $prefixSelf            True if the self value should be prefixed.
     * @param bool                       $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                       $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This builder.
     */
    public function callsWith(
        callable $callback,
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): self {
        if (null === $prefixSelf) {
            $parameters = $this->invocableInspector
                ->callbackReflector($callback)->getParameters();

            $prefixSelf = !empty($parameters) &&
                'phonySelf' === $parameters[0]->getName();
        }

        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        $this->requests[] = new CallRequest(
            $callback,
            $arguments,
            $prefixSelf,
            $suffixArgumentsObject,
            $suffixArguments
        );

        return $this;
    }

    /**
     * Add an argument callback to be called as part of the answer.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `0`.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int ...$indices The argument indices.
     *
     * @return $this This builder.
     */
    public function callsArgument(int ...$indices): self
    {
        if (empty($indices)) {
            $this->callsArgumentWith(0);
        } else {
            foreach ($indices as $index) {
                $this->callsArgumentWith($index);
            }
        }

        return $this;
    }

    /**
     * Add an argument callback to be called as part of the answer.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int                        $index                 The argument index.
     * @param Arguments|array<int,mixed> $arguments             The arguments.
     * @param bool                       $prefixSelf            True if the self value should be prefixed.
     * @param bool                       $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                       $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This builder.
     */
    public function callsArgumentWith(
        int $index = 0,
        $arguments = [],
        bool $prefixSelf = false,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): self {
        $invoker = $this->invoker;

        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        return $this->callsWith(
            function ($self, $incoming) use (
                $invoker,
                $index,
                $arguments,
                $prefixSelf,
                $suffixArgumentsObject,
                $suffixArguments
            ) {
                $callback = $incoming->get($index);

                $request = new CallRequest(
                    $callback,
                    $arguments,
                    $prefixSelf,
                    $suffixArgumentsObject,
                    $suffixArguments
                );
                $finalArguments = $request->finalArguments($self, $incoming);

                return $invoker->callWith($callback, $finalArguments);
            },
            [],
            true,
            true,
            false
        );
    }

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
     * @param mixed $indexOrValue The index, or value if no index is specified.
     * @param mixed $value        The value.
     *
     * @return $this This builder.
     */
    public function setsArgument($indexOrValue = null, $value = null): self
    {
        if (func_num_args() > 1) {
            $index = $indexOrValue;
        } else {
            $index = 0;
            $value = $indexOrValue;
        }

        if ($value instanceof InstanceHandle) {
            $value = $value->get();
        }

        return $this->callsWith(
            function ($arguments) use ($index, $value) {
                $arguments->set($index, $value);
            },
            [],
            false,
            true,
            false
        );
    }

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
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return $this This builder.
     */
    public function yields($keyOrValue = null, $value = null): self
    {
        $argumentCount = func_num_args();

        if ($argumentCount > 1) {
            $hasKey = true;
            $hasValue = true;
            $key = $keyOrValue;
        } elseif ($argumentCount > 0) {
            $hasKey = false;
            $hasValue = true;
            $key = null;
            $value = $keyOrValue;
        } else {
            $hasKey = false;
            $hasValue = false;
            $key = null;
        }

        if ($key instanceof InstanceHandle) {
            $key = $key->get();
        }

        if ($value instanceof InstanceHandle) {
            $value = $value->get();
        }

        $this->iterations[] = new GeneratorYieldIteration(
            $this->requests,
            $hasKey,
            $key,
            $hasValue,
            $value
        );
        $this->requests = [];

        return $this;
    }

    /**
     * Add a set of yielded values to the answer.
     *
     * @param iterable<mixed> $values The set of keys and values to yield.
     *
     * @return $this This builder.
     */
    public function yieldsFrom(iterable $values): self
    {
        $this->iterations[] =
            new GeneratorYieldFromIteration($this->requests, $values);
        $this->requests = [];

        return $this;
    }

    /**
     * End the generator by returning a value.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `null`.
     *
     * @param mixed ...$values The return values.
     *
     * @return Stub The stub.
     */
    public function returns(...$values): Stub
    {
        if (empty($values)) {
            $values = [null];
        }

        $value = $values[0];
        $argumentCount = count($values);
        $copies = [];

        for ($i = 1; $i < $argumentCount; ++$i) {
            $copies[$i] = clone $this;
        }

        if ($value instanceof InstanceHandle) {
            $value = $value->get();
        }

        $this->returnValue = $value;
        $this->returnsArgument = null;
        $this->returnsSelf = false;

        for ($i = 1; $i < $argumentCount; ++$i) {
            $this->stub
                ->doesWith($copies[$i]->answer(), [], true, true, false);

            $copies[$i]->returns($values[$i]);
        }

        return $this->stub;
    }

    /**
     * End the generator by returning an argument.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int $index The argument index.
     *
     * @return Stub The stub.
     */
    public function returnsArgument(int $index = 0): Stub
    {
        $this->returnsArgument = $index;

        return $this->stub;
    }

    /**
     * End the generator by returning the self value.
     *
     * @return Stub The stub.
     */
    public function returnsSelf(): Stub
    {
        $this->returnsSelf = true;

        return $this->stub;
    }

    /**
     * End the generator by throwing an exception.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `null`.
     *
     * @param Throwable|string|null ...$exceptions The exceptions, or messages, or nulls to throw generic exceptions.
     *
     * @return Stub The stub.
     */
    public function throws(...$exceptions): Stub
    {
        if (empty($exceptions)) {
            $exceptions = [new Exception()];
        }

        $exception = $exceptions[0];
        $argumentCount = count($exceptions);
        $copies = [];

        for ($i = 1; $i < $argumentCount; ++$i) {
            $copies[$i] = clone $this;
        }

        if (null === $exception) {
            $exception = new Exception();
        } elseif (is_string($exception)) {
            $exception = new Exception($exception);
        } elseif ($exception instanceof InstanceHandle) {
            /** @var Throwable */
            $exception = $exception->get();
        }

        $this->exception = $exception;

        for ($i = 1; $i < $argumentCount; ++$i) {
            $this->stub
                ->doesWith($copies[$i]->answer(), [], true, true, false);

            $copies[$i]->throws($exceptions[$i]);
        }

        return $this->stub;
    }

    /**
     * Get the answer.
     *
     * @return callable The answer.
     */
    public function answer(): callable
    {
        return function ($self, $arguments) {
            foreach ($this->iterations as $iteration) {
                foreach ($iteration->requests as $request) {
                    $this->invoker->callWith(
                        $request->callback(),
                        $request->finalArguments($self, $arguments)
                    );
                }

                if ($iteration instanceof GeneratorYieldFromIteration) {
                    foreach ($iteration->values as $key => $value) {
                        if ($key instanceof InstanceHandle) {
                            $key = $key->get();
                        }

                        if ($value instanceof InstanceHandle) {
                            $value = $value->get();
                        }

                        yield $key => $value;
                    }
                } else {
                    if ($iteration->hasKey) {
                        yield $iteration->key => $iteration->value;
                    } elseif ($iteration->hasValue) {
                        yield $iteration->value;
                    } else {
                        yield;
                    }
                }
            }

            foreach ($this->requests as $request) {
                $this->invoker->callWith(
                    $request->callback(),
                    $request->finalArguments($self, $arguments)
                );
            }

            if ($this->exception) {
                throw $this->exception;
            }

            if ($this->returnsSelf) {
                return $self;
            }

            if (null !== $this->returnsArgument) {
                return $arguments->get($this->returnsArgument);
            }

            return $this->returnValue;
        };
    }

    /**
     * Clone this builder.
     */
    public function __clone()
    {
        // explicitly break references
        foreach (get_object_vars($this) as $property => $value) {
            unset($this->$property);
            $this->$property = $value;
        }
    }

    /**
     * @var Stub
     */
    private $stub;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var array<int,CallRequest>
     */
    private $requests;

    /**
     * @var array<int,GeneratorYieldIteration|GeneratorYieldFromIteration>
     */
    private $iterations;

    /**
     * @var ?Throwable
     */
    private $exception;

    /**
     * @var mixed
     */
    private $returnValue;

    /**
     * @var ?int
     */
    private $returnsArgument;

    /**
     * @var bool
     */
    private $returnsSelf;
}
