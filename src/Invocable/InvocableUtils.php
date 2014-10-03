<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocable;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * A static utility class for inspecting callable values.
 *
 * @internal
 */
final class InvocableUtils
{
    /**
     * Calls a callback, maintaining reference parameters.
     *
     * @param callable                  $callback  The callback.
     * @param array<integer,mixed>|null $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public static function callWith($callback, array $arguments = null)
    {
        if ($callback instanceof InvocableInterface) {
            return $callback->invokeWith($arguments);
        }

        if (null === $arguments) {
            $arguments = array();
        }

        return call_user_func_array($callback, $arguments);
    }

    /**
     * Get the appropriate reflector for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return ReflectionFunctionAbstract The reflector.
     * @throws ReflectionException        If the callback cannot be reflected.
     */
    public static function callbackReflector($callback)
    {
        if (is_array($callback)) {
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        if (is_string($callback) && false !== strpos($callback, '::')) {
            list($className, $methodName) = explode('::', $callback);

            return new ReflectionMethod($className, $methodName);
        }

        return new ReflectionFunction($callback);
    }

    /**
     * Get the $this value for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return object|null The $this value.
     */
    public static function callbackThisValue($callback)
    {
        if (is_array($callback) && is_object($callback[0])) {
            return $callback[0];
        }

        if (is_object($callback)) {
            if (
                $callback instanceof Closure && self::isBoundClosureSupported()
            ) {
                $reflector = new ReflectionFunction($callback);

                return $reflector->getClosureThis();
            }

            return $callback;
        }

        return null;
    }

    /**
     * Returns true if bound closures are supported.
     *
     * @return boolean True if bound closures are supported.
     */
    public static function isBoundClosureSupported()
    {
        if (null === self::$isBoundClosureSupported) {
            $reflectorReflector = new ReflectionClass('ReflectionFunction');

            self::$isBoundClosureSupported = $reflectorReflector
                ->hasMethod('getClosureThis');
        }

        return self::$isBoundClosureSupported;
    }

    private static $isBoundClosureSupported;
}
