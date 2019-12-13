<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\Handle;
use ReflectionMethod;
use Throwable;

/**
 * A wrapper that allows calling of the parent method in mocks.
 */
class WrappedParentMethod implements WrappedMethod
{
    use WrappedMethodTrait;

    /**
     * Construct a new wrapped parent method.
     *
     * @param ReflectionMethod $callParentMethod The _callParent() method.
     * @param ReflectionMethod $method           The method.
     * @param Handle           $handle           The handle.
     */
    public function __construct(
        ReflectionMethod $callParentMethod,
        ReflectionMethod $method,
        Handle $handle
    ) {
        $this->callParentMethod = $callParentMethod;

        $this->constructWrappedMethod($method, $handle);
    }

    /**
     * Get the _callParent() method.
     *
     * @return ReflectionMethod The _callParent() method.
     */
    public function callParentMethod(): ReflectionMethod
    {
        return $this->callParentMethod;
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

        return $this->callParentMethod
            ->invoke($this->mock, $this->name, $arguments);
    }

    /**
     * @var ReflectionMethod
     */
    private $callParentMethod;
}
