<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Handle\Handle;
use ReflectionMethod;
use Throwable;

/**
 * A wrapper for custom methods.
 */
class WrappedCustomMethod implements WrappedMethod
{
    use WrappedMethodTrait;

    /**
     * Construct a new wrapped custom method.
     *
     * @param callable         $customCallback The custom callback.
     * @param ReflectionMethod $method         The method.
     * @param Handle           $handle         The handle.
     * @param Invoker          $invoker        The invoker to use.
     */
    public function __construct(
        callable $customCallback,
        ReflectionMethod $method,
        Handle $handle,
        Invoker $invoker
    ) {
        $this->customCallback = $customCallback;
        $this->invoker = $invoker;

        $this->constructWrappedMethod($method, $handle);
    }

    /**
     * Get the custom callback.
     *
     * @return callable The custom callback.
     */
    public function customCallback(): callable
    {
        return $this->customCallback;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array<int,mixed> $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invokeWith($arguments = [])
    {
        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        return $this->invoker->callWith($this->customCallback, $arguments);
    }

    /**
     * @var callable
     */
    private $customCallback;

    /**
     * @var Invoker
     */
    private $invoker;
}
