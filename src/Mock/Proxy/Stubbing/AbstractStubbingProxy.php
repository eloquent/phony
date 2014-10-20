<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Stubbing;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Proxy\AbstractProxy;
use Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException;
use Eloquent\Phony\Mock\Proxy\Exception\UndefinedPropertyException;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * An abstract base class for implementing stubbing proxies.
 *
 * @internal
 */
abstract class AbstractStubbingProxy extends AbstractProxy implements
    StubbingProxyInterface
{
    /**
     * Turn the mock into a full mock.
     *
     * @return StubbingProxyInterface This proxy.
     */
    public function full()
    {
        foreach ($this->stubs as $stub) {
            $stub->callback()->with($this->wildcard)->returns()
                ->with($this->wildcard);
        }

        return $this;
    }

    /**
     * Get a stub verifier, and modify its current criteria to match the
     * supplied arguments.
     *
     * @param string               $name      The method name.
     * @param array<integer,mixed> $arguments The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __call($name, array $arguments)
    {
        try {
            $stub = $this->stub($name);
        } catch (UndefinedMethodStubException $e) {
            throw new UndefinedMethodException(get_called_class(), $name, $e);
        }

        return call_user_func_array(
            array($stub->callback(), 'with'),
            $arguments
        );
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __get($name)
    {
        try {
            $stub = $this->stub($name);
        } catch (UndefinedMethodStubException $e) {
            throw new UndefinedPropertyException(get_called_class(), $name, $e);
        }

        return $stub;
    }
}
