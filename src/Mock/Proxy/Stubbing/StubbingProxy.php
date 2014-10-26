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

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Proxy\AbstractInstanceProxy;
use Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * A proxy for stubbing a mock.
 *
 * @internal
 */
class StubbingProxy extends AbstractInstanceProxy implements
    InstanceStubbingProxyInterface
{
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

        return call_user_func_array(array($stub, 'with'), $arguments);
    }
}
