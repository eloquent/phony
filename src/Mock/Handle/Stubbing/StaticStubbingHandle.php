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

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Handle\AbstractStaticHandle;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * A handle for stubbing a mock class.
 */
class StaticStubbingHandle extends AbstractStaticHandle implements
    StaticStubbingHandleInterface
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
