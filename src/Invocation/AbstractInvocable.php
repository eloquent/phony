<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use Throwable;

/**
 * An abstract base class for implementing invocables.
 */
abstract class AbstractInvocable implements Invocable
{
    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invoke(...$arguments)
    {
        return $this->invokeWith($arguments);
    }

    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function __invoke(...$arguments)
    {
        return $this->invokeWith($arguments);
    }
}
