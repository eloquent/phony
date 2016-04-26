<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle\Stubbing;

use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Mock\Handle\AbstractInstanceHandle;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Stub\StubVerifier;

/**
 * A handle for stubbing a mock.
 */
class InstanceStubbingHandle extends AbstractInstanceHandle implements
    InstanceHandle,
    StubbingHandle
{
    /**
     * Get a stub verifier, and modify its current criteria to match the
     * supplied arguments.
     *
     * @param string $name      The method name.
     * @param array  $arguments The arguments.
     *
     * @return StubVerifier  The stub verifier.
     * @throws MockException If the stub does not exist.
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
