<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Call\Arguments;
use Throwable;

/**
 * Invokes callbacks, maintaining reference parameters.
 */
class Invoker
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Calls a callback, maintaining reference parameters.
     *
     * @param callable  $callback  The callback.
     * @param Arguments $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function callWith(callable $callback, Arguments $arguments)
    {
        if ($callback instanceof Invocable) {
            return $callback->invokeWith($arguments);
        }

        $arguments = $arguments->all();

        return $callback(...$arguments);
    }

    /**
     * @var ?self
     */
    private static $instance;
}
