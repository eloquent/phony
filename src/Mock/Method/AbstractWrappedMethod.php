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
use ReflectionMethod;

/**
 * An abstract base class for implementing wrapped methods.
 *
 * @internal
 */
abstract class AbstractWrappedMethod extends AbstractWrappedInvocable
{
    /**
     * Construct a new wrapped method.
     *
     * @param ReflectionMethod   $method The method.
     * @param MockInterface|null $mock   The mock.
     */
    public function __construct(
        ReflectionMethod $method,
        MockInterface $mock = null
    ) {
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

    protected $method;
    protected $mock;
    protected $name;
}
