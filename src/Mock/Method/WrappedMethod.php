<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Mock\MockInterface;
use Exception;
use ReflectionMethod;

/**
 * A wrapper that allows calling of the parent method in mocks.
 *
 * @internal
 */
class WrappedMethod extends AbstractWrappedInvocable
{
    /**
     * Construct a new wrapped method.
     *
     * @param ReflectionMethod   $callParentMethod The callParent() method.
     * @param ReflectionMethod   $method           The method.
     * @param MockInterface|null $mock             The mock.
     */
    public function __construct(
        ReflectionMethod $callParentMethod,
        ReflectionMethod $method,
        MockInterface $mock = null
    ) {
        $this->callParentMethod = $callParentMethod;
        $this->method = $method;
        $this->mock = $mock;
        $this->name = $method->getName();

        if ($this->method->isStatic()) {
            $callback = array(
                $method->getDeclaringClass()->getName(),
                $this->name
            );
        } else {
            $callback = array($mock, $this->name);
        }

        parent::__construct($callback);
    }

    /**
     * Get the callParent() method.
     *
     * @return ReflectionMethod The callParent() method.
     */
    public function callParentMethod()
    {
        return $this->callParentMethod;
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
     * Get the mock.
     *
     * @return MockInterface|null The mock.
     */
    public function mock()
    {
        return $this->mock;
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

        return $this->callParentMethod
            ->invoke($this->mock, $this->name, $arguments);
    }

    private $callParentMethod;
    private $method;
    private $mock;
    private $name;
}
