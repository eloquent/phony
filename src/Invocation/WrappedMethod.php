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

use Exception;
use ReflectionMethod;

/**
 * Wraps a method reflector, bypassing access modifier restrictions if
 * necessary.
 *
 * @internal
 */
class WrappedMethod extends AbstractWrappedInvocable
{
    /**
     * Construct a new wrapped method.
     *
     * @param ReflectionMethod $method   The method.
     * @param object|null      $instance The instance.
     * @param string|null      $name     The name.
     */
    public function __construct(
        ReflectionMethod $method,
        $instance = null,
        $name = null
    ) {
        $realName = $method->getName();

        if (null === $name) {
            $name = $realName;
        }

        $this->method = new ReflectionMethod(
            $method->getDeclaringClass()->getName(),
            $realName
        );
        $this->instance = $instance;

        if (!$this->method->isPublic()) {
            $this->method->setAccessible(true);
        }

        parent::__construct(array($instance, $name));

        $this->name = $name;
    }

    /**
     * Get the method.
     *
     * @return ReflectionMethod The method.
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Get the instance.
     *
     * @return object|null The instance.
     */
    public function instance()
    {
        return $this->instance;
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
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param array<integer,mixed>|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invokeWith(array $arguments = null)
    {
        if (null === $arguments) {
            $arguments = array();
        }

        return $this->method->invokeArgs($this->instance, $arguments);
    }

    private $method;
    private $instance;
    private $name;
}
