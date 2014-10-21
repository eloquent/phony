<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Exception;

/**
 * Invokes callbacks, maintaining reference parameters.
 *
 * @internal
 */
class Invoker implements InvokerInterface
{
    /**
     * Get the static instance of this invoker.
     *
     * @return InvokerInterface The static invoker.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Calls a callback, maintaining reference parameters.
     *
     * @param callable                                     $callback  The callback.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function callWith($callback, $arguments = null)
    {
        if ($callback instanceof InvocableInterface) {
            return $callback->invokeWith($arguments);
        }

        return call_user_func_array(
            $callback,
            Arguments::adapt($arguments)->all()
        );
    }

    private static $instance;
}
