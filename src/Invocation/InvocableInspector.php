<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionType;

/**
 * Utilities for inspecting invocables.
 */
class InvocableInspector
{
    /**
     * Get the static instance of this inspector.
     *
     * @return InvocableInspector The static inspector.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the appropriate reflector for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return ReflectionFunctionAbstract The reflector.
     * @throws ReflectionException        If the callback cannot be reflected.
     */
    public function callbackReflector(
        callable $callback
    ): ReflectionFunctionAbstract {
        while ($callback instanceof WrappedInvocable) {
            $callback = $callback->callback();
        }

        if (is_array($callback)) {
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        if (is_string($callback) && false !== strpos($callback, '::')) {
            list($className, $methodName) = explode('::', $callback);

            return new ReflectionMethod($className, $methodName);
        }

        if (is_object($callback) && !$callback instanceof Closure) {
            return new ReflectionMethod($callback, '__invoke');
        }

        return new ReflectionFunction($callback);
    }

    /**
     * Get the return type for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return ReflectionType|null The return type, or null if no return type is defined.
     * @throws ReflectionException If the callback cannot be reflected.
     */
    public function callbackReturnType(callable $callback)
    {
        return $this->callbackReflector($callback)->getReturnType();
    }

    private static $instance;
}
