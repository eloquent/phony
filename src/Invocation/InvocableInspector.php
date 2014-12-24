<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Utilities for inspecting invocables.
 *
 * @internal
 */
class InvocableInspector implements InvocableInspectorInterface
{
    /**
     * Get the static instance of this inspector.
     *
     * @return InvocableInspectorInterface The static inspector.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new invocable inspector.
     */
    public function __construct()
    {
        $reflectorReflector = new ReflectionClass('ReflectionFunction');
        $this->isBoundClosureSupported =
            $reflectorReflector->hasMethod('getClosureThis');
    }

    /**
     * Get the appropriate reflector for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return ReflectionFunctionAbstract The reflector.
     * @throws ReflectionException        If the callback cannot be reflected.
     */
    public function callbackReflector($callback)
    {
        while ($callback instanceof WrappedInvocableInterface) {
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
            if (method_exists($callback, '__invoke')) {
                return new ReflectionMethod($callback, '__invoke');
            }

            throw new ReflectionException('Invalid callback.');
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
    public function callbackThisValue($callback)
    {
        if (is_array($callback) && is_object($callback[0])) {
            return $callback[0];
        }

        if (is_object($callback)) {
            if ($callback instanceof Closure) {
                if ($this->isBoundClosureSupported()) {
                    $reflector = new ReflectionFunction($callback);

                    return $reflector->getClosureThis();
                }
            } elseif (method_exists($callback, '__invoke')) {
                return $callback;
            }
        }

        return;
    }

    /**
     * Returns true if bound closures are supported.
     *
     * @return boolean True if bound closures are supported.
     */
    public function isBoundClosureSupported()
    {
        return $this->isBoundClosureSupported;
    }

    protected $isBoundClosureSupported;
    private static $instance;
}
