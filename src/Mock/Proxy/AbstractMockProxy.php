<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use BadMethodCallException;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * An abstract base class for implementing mock proxies.
 *
 * @internal
 */
abstract class AbstractMockProxy implements MockProxyInterface
{
    /**
     * Construct a new static mock proxy.
     *
     * @param string                              $className The class name.
     * @param array<string,StubVerifierInterface> $stubs     The stubs.
     */
    public function __construct($className, array $stubs)
    {
        $this->className = $className;
        $this->stubs = $stubs;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className()
    {
        return $this->className;
    }

    /**
     * Get the stubs.
     *
     * @return array<string,StubVerifierInterface> The stubs.
     */
    public function stubs()
    {
        return $this->stubs;
    }

    /**
     * Turn the mock into a full mock.
     *
     * @return MockProxyInterface This proxy.
     */
    public function full()
    {
        foreach ($this->stubs as $stub) {
            $stub->with()->returns();
        }

        return $this;
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function stub($name)
    {
        if (isset($this->stubs[$name])) {
            return $this->stubs[$name];
        }

        throw new UndefinedMethodStubException($this->className, $name);
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name      The method name.
     * @param array  $arguments Ignored.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the stub does not exist.
     */
    public function __call($name, array $arguments)
    {
        try {
            $stub = $this->stub($name);
        } catch (UndefinedMethodStubException $e) {
            throw new BadMethodCallException(
                sprintf(
                    'Call to undefined method %s::%s().',
                    get_called_class(),
                    $name
                ),
                0,
                $e
            );
        }

        return $stub;
    }

    private $className;
    private $stubs;
}