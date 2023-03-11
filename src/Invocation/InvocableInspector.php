<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use Closure;
use Eloquent\Phony\Mock\Method\WrappedMethod;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Utilities for inspecting invocables.
 */
class InvocableInspector
{
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
            if ($callback instanceof WrappedMethod) {
                return $callback->method();
            }

            $callback = $callback->callback();
        }

        if ($callback instanceof Closure) {
            return new ReflectionFunction($callback);
        }

        if (is_object($callback)) {
            return new ReflectionMethod($callback, '__invoke');
        }

        if (is_array($callback)) {
            list($classNameOrObject, $methodName) = $callback;

            if (str_starts_with($methodName, 'parent::')) {
                $class = new ReflectionClass($classNameOrObject);
                /** @var ReflectionClass<object> $parentClass */
                $parentClass = $class->getParentClass();

                return new ReflectionMethod(
                    $parentClass->getName(),
                    substr($methodName, 8),
                );
            }

            return new ReflectionMethod(...$callback);
        }

        if (is_string($callback) && str_contains($callback, '::')) {
            return new ReflectionMethod($callback);
        }

        /** @var string $callback */

        return new ReflectionFunction($callback);
    }
}
