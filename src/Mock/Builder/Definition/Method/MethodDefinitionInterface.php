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

use Closure;
use ReflectionMethod;

/**
 * The interface implemented by method definitions.
 */
interface MethodDefinitionInterface
{
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
     * @return integer The access level.
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
     * @return ReflectionMethod|null The method, or null if this definition is custom.
     */
    public function method();

    /**
     * Get the closure.
     *
     * @return Closure|null The closure, or null if this definition is a real method.
     */
    public function closure();
}
