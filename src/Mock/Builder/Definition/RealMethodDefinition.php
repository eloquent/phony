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
 * Represents a real method definition.
 *
 * @internal
 */
class RealMethodDefinition implements MethodDefinitionInterface
{
    const ACCESS_LEVEL_PUBLIC = 0;
    const ACCESS_LEVEL_PROTECTED = 1;

    /**
     * Construct a new real method definition.
     *
     * @param ReflectionMethod $method The method.
     * @param string|null      $name   The name.
     */
    public function __construct(ReflectionMethod $method, $name = null)
    {
        if (null === $name) {
            $name = $method->getName();
        }

        $this->method = $method;
        $this->name = $name;
    }

    /**
     * Returns true if this method is static.
     *
     * @return boolean True if this method is static.
     */
    public function isStatic()
    {
        return $this->method->isStatic();
    }

    /**
     * Returns true if this method is custom.
     *
     * @return boolean True if this method is custom.
     */
    public function isCustom()
    {
        return false;
    }

    /**
     * Get the access level.
     *
     * @return integer The access level.
     */
    public function accessLevel()
    {
        if ($this->method->isPublic()) {
            return self::ACCESS_LEVEL_PUBLIC;
        }

        return self::ACCESS_LEVEL_PROTECTED;
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
        return $this->method;
    }

    /**
     * Get the closure.
     *
     * @return Closure|null The closure, or null if this definition is a real method.
     */
    public function closure()
    {
        return null;
    }

    private $method;
    private $name;
}
