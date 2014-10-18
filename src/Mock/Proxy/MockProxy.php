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
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionProperty;

/**
 * A proxy for controlling a mock.
 *
 * @internal
 */
class MockProxy implements MockProxyInterface
{
    /**
     * Construct a new mock proxy.
     *
     * @param MockInterface                            $mock  The mock.
     * @param array<string,StubVerifierInterface>|null $stubs The stubs.
     */
    public function __construct(MockInterface $mock, array $stubs = null)
    {
        if (null === $stubs) {
            $stubsProperty = new ReflectionProperty($mock, '_stubs');
            $stubsProperty->setAccessible(true);
            $stubs = $stubsProperty->getValue($mock);
        }

        $this->mock = $mock;
        $this->stubs = $stubs;
    }

    /**
     * Get the mock.
     *
     * @return MockInterface The mock.
     */
    public function mock()
    {
        return $this->mock;
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

        throw new UndefinedMethodStubException(get_class($this->mock), $name);
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
                    get_class(),
                    $name
                ),
                0,
                $e
            );
        }

        return $stub;
    }

    private $mock;
    private $stubs;
}
