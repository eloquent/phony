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

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
use Error;
use Exception;
use ReflectionMethod;

/**
 * A wrapper that allows calling of the parent method in mocks.
 */
class WrappedMethod extends AbstractWrappedMethod
{
    /**
     * Construct a new wrapped method.
     *
     * @param ReflectionMethod $callParentMethod The _callParent() method.
     * @param ReflectionMethod $method           The method.
     * @param ProxyInterface   $proxy            The proxy.
     */
    public function __construct(
        ReflectionMethod $callParentMethod,
        ReflectionMethod $method,
        ProxyInterface $proxy
    ) {
        $this->callParentMethod = $callParentMethod;

        parent::__construct($method, $proxy);
    }

    /**
     * Get the _callParent() method.
     *
     * @return ReflectionMethod The _callParent() method.
     */
    public function callParentMethod()
    {
        return $this->callParentMethod;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param ArgumentsInterface|array $arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invokeWith($arguments = array())
    {
        return $this->callParentMethod
            ->invoke($this->mock, $this->name, Arguments::adapt($arguments));
    }

    private $callParentMethod;
}
