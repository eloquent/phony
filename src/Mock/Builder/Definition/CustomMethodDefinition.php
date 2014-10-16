<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition;

use Closure;
use ReflectionMethod;

/**
 * Represents a custom method definition.
 *
 * @internal
 */
class CustomMethodDefinition implements MethodDefinitionInterface
{
    /**
     * Construct a new custom method definition.
     *
     * @param boolean      $isStatic True if this method is static.
     * @param string       $name     The name.
     * @param Closure|null $closure  The closure.
     */
    public function __construct($isStatic, $name, Closure $closure = null)
    {
        if (null === $closure) {
            $closure = function () {};
        }

        $this->isStatic = $isStatic;
        $this->name = $name;
        $this->closure = $closure;
    }

    /**
     * Returns true if this method is static.
     *
     * @return boolean True if this method is static.
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    /**
     * Returns true if this method is custom.
     *
     * @return boolean True if this method is custom.
     */
    public function isCustom()
    {
        return true;
    }

    /**
     * Get the access level.
     *
     * @return integer The access level.
     */
    public function accessLevel()
    {
        return RealMethodDefinition::ACCESS_LEVEL_PUBLIC;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the method.
     *
     * @return ReflectionMethod|null The method, or null if this definition is custom.
     */
    public function method()
    {
        return null;
    }

    /**
     * Get the closure.
     *
     * @return Closure|null The closure, or null if this definition is a real method.
     */
    public function closure()
    {
        return $this->closure;
    }

    private $isStatic;
    private $name;
    private $closure;
}
