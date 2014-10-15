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
 * Bypasses method access modifier restrictions.
 *
 * @internal
 */
class AccessibleMethod extends AbstractInvocable
{
    /**
     * Construct a new accessible method.
     *
     * @param ReflectionMethod $method   The method.
     * @param object|null      $instance The instance.
     */
    public function __construct(ReflectionMethod $method, $instance = null)
    {
        $this->method = new ReflectionMethod(
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );
        $this->instance = $instance;

        $this->method->setAccessible(true);
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
}
