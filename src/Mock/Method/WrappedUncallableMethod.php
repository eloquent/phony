<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Mock\Handle\Handle;
use ReflectionMethod;
use Throwable;

/**
 * A wrapper for uncallable methods.
 */
class WrappedUncallableMethod implements WrappedMethod
{
    use WrappedMethodTrait;

    /**
     * Construct a new wrapped uncallable method.
     *
     * @param ReflectionMethod $method      The method.
     * @param Handle           $handle      The handle.
     * @param mixed            $returnValue The return value.
     */
    public function __construct(
        ReflectionMethod $method,
        Handle $handle,
        $returnValue
    ) {
        $this->returnValue = $returnValue;

        $this->constructWrappedMethod($method, $handle);
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invokeWith($arguments = [])
    {
        return $this->returnValue;
    }

    private $returnValue;
}
