<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Verification;

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Proxy\AbstractInstanceProxy;
use Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException;
use Exception;

/**
 * A proxy for verifying a mock.
 */
class VerificationProxy extends AbstractInstanceProxy implements
    InstanceVerificationProxyInterface
{
    /**
     * Throws an exception unless the specified method was called with the
     * supplied arguments.
     *
     * @param string $name      The method name.
     * @param array  $arguments The arguments.
     *
     * @return $this                  This proxy.
     * @throws MockExceptionInterface If the stub does not exist.
     * @throws Exception              If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function __call($name, array $arguments)
    {
        try {
            $stub = $this->stub($name);
        } catch (UndefinedMethodStubException $e) {
            throw new UndefinedMethodException(get_called_class(), $name, $e);
        }

        call_user_func_array(array($stub, 'calledWith'), $arguments);

        return $this;
    }
}
