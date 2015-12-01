<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition\Method;

use ReflectionFunction;
use ReflectionFunctionAbstract;

/**
 * Represents a custom method definition.
 */
class CustomMethodDefinition implements MethodDefinitionInterface
{
    /**
     * Construct a new custom method definition.
     *
     * @param boolean       $isStatic True if this method is static.
     * @param string        $name     The name.
     * @param callable|null $callback The callback.
     */
    public function __construct($isStatic, $name, $callback = null)
    {
        if (null === $callback) {
            $callback = function () {};
        }

        $this->isStatic = $isStatic;
        $this->name = $name;
        $this->method = new ReflectionFunction($callback);
        $this->callback = $callback;
    }

    /**
     * Returns true if this method is callable.
     *
     * @return boolean True if this method is callable.
     */
    public function isCallable()
    {
        return true;
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
     * @return string The access level.
     */
    public function accessLevel()
    {
        return 'public';
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
     * @return ReflectionFunctionAbstract The method.
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Get the callback.
     *
     * @return callable|null The callback, or null if this is a real method.
     */
    public function callback()
    {
        return $this->callback;
    }

    private $isStatic;
    private $name;
    private $method;
    private $callback;
}
