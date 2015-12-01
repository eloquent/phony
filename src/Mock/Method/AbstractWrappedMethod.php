<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
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
     * @param ProxyInterface   $proxy  The proxy.
     */
    public function __construct(ReflectionMethod $method, ProxyInterface $proxy)
    {
        $this->method = $method;
        $this->proxy = $proxy;
        $this->name = $method->getName();

        if ($method->isStatic()) {
            $this->mock = null;
            $callback = array(
                $method->getDeclaringClass()->getName(),
                $this->name,
            );
        } else {
            $this->mock = $proxy->mock();
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
     * Get the proxy.
     *
     * @return ProxyInterface The proxy.
     */
    public function proxy()
    {
        return $this->proxy;
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
    protected $proxy;
    protected $mock;
    protected $name;
}
