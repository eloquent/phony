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

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Handle\HandleInterface;
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
     * @param HandleInterface  $handle           The handle.
     */
    public function __construct(
        ReflectionMethod $callParentMethod,
        ReflectionMethod $method,
        HandleInterface $handle
    ) {
        $this->callParentMethod = $callParentMethod;

        parent::__construct($method, $handle);
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
        if (!$arguments instanceof ArgumentsInterface) {
            $arguments = new Arguments($arguments);
        }

        return $this->callParentMethod
            ->invoke($this->mock, $this->name, $arguments);
    }

    private $callParentMethod;
}
