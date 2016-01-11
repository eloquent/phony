<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Stubbing;

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Proxy\AbstractInstanceProxy;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * A proxy for stubbing a mock.
 */
class StubbingProxy extends AbstractInstanceProxy implements
    InstanceStubbingProxyInterface
{
    /**
     * Get a stub verifier, and modify its current criteria to match the
     * supplied arguments.
     *
     * @param string $name      The method name.
     * @param array  $arguments The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __call($name, array $arguments)
    {
        $key = strtolower($name);

        if (isset($this->state->stubs->$key)) {
            $stub = $this->state->stubs->$key;
        } else {
            $stub = $this->state->stubs->$key = $this->createStub($name);
        }

        return call_user_func_array(array($stub, 'with'), $arguments);
    }
}
