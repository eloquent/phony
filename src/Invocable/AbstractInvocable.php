<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocable;

use Exception;

/**
 * An abstract base class for implementing invocables.
 *
 * @internal
 */
abstract class AbstractInvocable implements InvocableInterface
{
    /**
     * Invoke this object.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invoke()
    {
        return $this->invokeWith(func_get_args());
    }

    /**
     * Invoke this object.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function __invoke()
    {
        return $this->invokeWith(func_get_args());
    }
}
