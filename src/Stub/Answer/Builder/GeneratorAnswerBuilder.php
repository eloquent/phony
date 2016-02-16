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

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Mock\Handle\InstanceHandleInterface;
use Eloquent\Phony\Stub\Answer\CallRequest;
use Eloquent\Phony\Stub\StubInterface;
use Exception;
use RuntimeException;

/**
 * Builds generator stub answers.
 */
class GeneratorAnswerBuilder implements GeneratorAnswerBuilderInterface
{
    /**
     * Construct a new generator answer builder.
     *
     * @param StubInterface               $stub                       The stub.
     * @param array                       $values                     An array of keys and values to yield.
     * @param boolean                     $isGeneratorReturnSupported True if generator return values are supported.
     * @param InvocableInspectorInterface $invocableInspector         The invocable inspector to use.
     * @param InvokerInterface            $invoker                    The invoker to use.
     */
    public function __construct(
        StubInterface $stub,
        array $values,
        $isGeneratorReturnSupported,
        InvocableInspectorInterface $invocableInspector,
        InvokerInterface $invoker
    ) {
        $this->stub = $stub;
        $this->isGeneratorReturnSupported = $isGeneratorReturnSupported;
        $this->invocableInspector = $invocableInspector;
        $this->invoker = $invoker;

        $this->requests = array();
        $this->iterations = array();
        $this->returnsSelf = false;

        foreach ($values as $key => $value) {
            $this->iterations[] =
                array(true, $key, true, $value, $this->requests);
        }
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
     * Get the invocable inspector.
     *
     * @return InvocableInspectorInterface The invocable inspector.
     */
    public function invocableInspector()
    {
        return $this->invocableInspector;
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
     * Add a callback to be called as part of the answer.
     *
     * @param callable $callback The callback.
     * @param callable ...$additionalCallbacks Additional callbacks.
     *
     * @return $this This builder.
     */
    public function calls($callback)
    {
        foreach (func_get_args() as $callback) {
            $this->callsWith($callback);
        }

        return $this;
    }

    /**
     * Add a callback to be called as part of the answer.
     *
     * This method supports reference parameters.
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
    ) {
        if (null === $prefixSelf) {
            $parameters = $this->invocableInspector
                ->callbackReflector($callback)->getParameters();

            $prefixSelf = $parameters &&
                'phonySelf' === $parameters[0]->getName();
        }

        if (!$arguments instanceof ArgumentsInterface) {
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
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The argument index.
     * @param integer ...$additionalIndices Additional argument indices to call.
     *
     * @return $this This builder.
     */
    public function callsArgument($index = 0)
    {
        if ($arguments = func_get_args()) {
            foreach ($arguments as $index) {
                $this->callsArgumentWith($index);
            }
        } else {
            $this->callsArgumentWith(0);
        }

        return $this;
    }

    /**
     * Add an argument callback to be called as part of the answer.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
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
    ) {
        $invoker = $this->invoker;

        if (!$arguments instanceof ArgumentsInterface) {
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
                if (!$incoming->has($index)) {
                    return;
                }

                $callback = $incoming->get($index);

                if (!is_callable($callback)) {
                    return;
                }

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
            array(),
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
    public function setsArgument($indexOrValue = null, $value = null)
    {
        if (func_num_args() > 1) {
            $index = $indexOrValue;
        } else {
            $index = 0;
            $value = $indexOrValue;
        }

        if (
            $value instanceof InstanceHandleInterface &&
            $value->isAdaptable()
        ) {
            $value = $value->mock();
        }

        return $this->callsWith(
            function ($arguments) use ($index, $value) {
                if ($arguments->has($index)) {
                    $arguments->set($index, $value);
                }
            },
            array(),
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
    public function yields($keyOrValue = null, $value = null)
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

        $this->iterations[] =
            array($hasKey, $key, $hasValue, $value, $this->requests);
        $this->requests = array();

        return $this;
    }

    /**
     * End the generator by returning a value.
     *
     * @param mixed $value The return value.
     *
     * @return StubInterface    The stub.
     * @throws RuntimeException If the current runtime does not support the supplied return value.
     */
    public function returns($value = null)
    {
        // @codeCoverageIgnoreStart
        if (
            $value instanceof InstanceHandleInterface &&
            $value->isAdaptable()
        ) {
            $value = $value->mock();
        }
        // @codeCoverageIgnoreEnd

        if ($this->isGeneratorReturnSupported || null === $value) {
            $this->returnValue = $value;
            $this->returnsArgument = null;
            $this->returnsSelf = false;
        } else {
            throw new RuntimeException(
                'The current runtime does not support the supplied generator ' .
                'return value.'
            );
        }

        return $this->stub;
    }

    /**
     * End the generator by returning an argument.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The argument index.
     *
     * @return StubInterface The stub.
     */
    public function returnsArgument($index = 0)
    {
        if (!$this->isGeneratorReturnSupported) {
            throw new RuntimeException(
                'The current runtime does not support generator return values.'
            );
        }

        // @codeCoverageIgnoreStart
        $this->returnsArgument = $index;

        return $this->stub;
        // @codeCoverageIgnoreEnd
    }

    /**
     * End the generator by returning the self value.
     *
     * @return StubInterface The stub.
     */
    public function returnsSelf()
    {
        if (!$this->isGeneratorReturnSupported) {
            throw new RuntimeException(
                'The current runtime does not support generator return values.'
            );
        }

        // @codeCoverageIgnoreStart
        $this->returnsSelf = true;

        return $this->stub;
        // @codeCoverageIgnoreEnd
    }

    /**
     * End the generator by throwing an exception.
     *
     * @param Exception|Error|string|null $exception The exception, or message, or null to throw a generic exception.
     *
     * @return StubInterface The stub.
     */
    public function throws($exception = null)
    {
        if (is_string($exception)) {
            $exception = new Exception($exception);
        } elseif (
            $exception instanceof InstanceHandleInterface &&
            $exception->isAdaptable()
        ) {
            $exception = $exception->mock();
        } elseif (!$exception) {
            $exception = new Exception();
        }

        $this->exception = $exception;

        return $this->stub;
    }

    /**
     * Get the answer.
     *
     * @return callable The answer.
     */
    public function answer()
    {
        if ($this->isGeneratorReturnSupported) {
            // @codeCoverageIgnoreStart
            return GeneratorAnswerBuilderDetailWithReturn::answer(
                $this->iterations,
                $this->requests,
                $this->exception,
                $this->returnValue,
                $this->returnsArgument,
                $this->returnsSelf,
                $this->invoker
            );
            // @codeCoverageIgnoreEnd
        }

        return GeneratorAnswerBuilderDetail::answer(
            $this->iterations,
            $this->requests,
            $this->exception,
            $this->returnValue,
            $this->returnsArgument,
            $this->returnsSelf,
            $this->invoker
        );
    }

    private $stub;
    private $isGeneratorReturnSupported;
    private $invocableInspector;
    private $invoker;
    private $requests;
    private $iterations;
    private $exception;
    private $returnValue;
    private $returnsArgument;
    private $returnsSelf;
}
