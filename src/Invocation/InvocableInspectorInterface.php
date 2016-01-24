<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use ReflectionException;
use ReflectionFunctionAbstract;

/**
 * The interface implemented by invocable inspectors.
 */
interface InvocableInspectorInterface
{
    /**
     * Get the appropriate reflector for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return ReflectionFunctionAbstract The reflector.
     * @throws ReflectionException        If the callback cannot be reflected.
     */
    public function callbackReflector($callback);

    /**
     * Get the $this value for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return object|null The $this value.
     */
    public function callbackThisValue($callback);

    /**
     * Returns true if bound closures are supported.
     *
     * @return boolean True if bound closures are supported.
     */
    public function isBoundClosureSupported();
}
