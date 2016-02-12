<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Mock\Handle\HandleInterface;
use Eloquent\Phony\Mock\Handle\StaticHandleInterface;
use Eloquent\Phony\Mock\MockInterface;
use ReflectionMethod;

/**
 * An abstract base class for implementing wrapped methods.
 */
abstract class AbstractWrappedMethod extends AbstractWrappedInvocable implements
    WrappedMethodInterface
{
    /**
     * Construct a new wrapped method.
     *
     * @param ReflectionMethod $method The method.
     * @param HandleInterface  $handle The handle.
     */
    public function __construct(ReflectionMethod $method, HandleInterface $handle)
    {
        $this->method = $method;
        $this->handle = $handle;
        $this->name = $method->getName();

        if ($handle instanceof StaticHandleInterface) {
            $this->mock = null;
            $callback = array(
                $method->getDeclaringClass()->getName(),
                $this->name,
            );
        } else {
            $this->mock = $handle->mock();
            $callback = array($this->mock, $this->name);
        }

        parent::__construct($callback);
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
     * Get the name.
     *
     * @return string The name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the handle.
     *
     * @return HandleInterface The handle.
     */
    public function handle()
    {
        return $this->handle;
    }

    /**
     * Get the mock.
     *
     * @return MockInterface|null The mock.
     */
    public function mock()
    {
        return $this->mock;
    }

    protected $method;
    protected $handle;
    protected $mock;
    protected $name;
}
