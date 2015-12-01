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
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
use Error;
use Exception;
use ReflectionMethod;

/**
 * A wrapper for custom methods.
 */
class WrappedCustomMethod extends AbstractWrappedMethod
{
    /**
     * Construct a new wrapped custom method.
     *
     * @param callable              $customCallback The custom callback.
     * @param ReflectionMethod      $method         The method.
     * @param ProxyInterface        $proxy          The proxy.
     * @param InvokerInterface|null $invoker        The invoker to use.
     */
    public function __construct(
        $customCallback,
        ReflectionMethod $method,
        ProxyInterface $proxy,
        InvokerInterface $invoker = null
    ) {
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }

        $this->customCallback = $customCallback;
        $this->invoker = $invoker;

        parent::__construct($method, $proxy);
    }

    /**
     * Get the custom callback.
     *
     * @return ReflectionMethod The custom callback.
     */
    public function customCallback()
    {
        return $this->customCallback;
    }

    /**
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param ArgumentsInterface|array|null The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invokeWith($arguments = null)
    {
        return $this->invoker->callWith($this->customCallback, $arguments);
    }

    private $customCallback;
    private $invoker;
}
