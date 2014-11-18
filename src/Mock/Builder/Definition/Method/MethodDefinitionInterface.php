<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition\Method;

use ReflectionFunctionAbstract;

/**
 * The interface implemented by method definitions.
 */
interface MethodDefinitionInterface
{
    /**
     * Returns true if this method is callable.
     *
     * @return boolean True if this method is callable.
     */
    public function isCallable();

    /**
     * Returns true if this method is static.
     *
     * @return boolean True if this method is static.
     */
    public function isStatic();

    /**
     * Returns true if this method is custom.
     *
     * @return boolean True if this method is custom.
     */
    public function isCustom();

    /**
     * Get the access level.
     *
     * @return string The access level.
     */
    public function accessLevel();

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function name();

    /**
     * Get the method.
     *
     * @return ReflectionFunctionAbstract The method.
     */
    public function method();

    /**
     * Get the callback.
     *
     * @return callable|null The callback, or null if this is a real method.
     */
    public function callback();
}
