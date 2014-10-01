<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Exception;

/**
 * The interface implemented by stubs.
 */
interface StubInterface
{
    /**
     * Modify the current criteria to match the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function with();

    /**
     * Modify the current criteria to match the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function withExactly();

    /**
     * Add a callback as an answer.
     *
     * @param callable $callback                The callback.
     * @param callable $additionalCallbacks,... Additional callbacks for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function does($callback);

    /**
     * Add an answer that returns a value.
     *
     * @param mixed $value                The return value.
     * @param mixed $additionalValues,... Additional return values for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function returns($value = null);

    /**
     * Add an answer that returns an argument.
     *
     * @param integer|null $index The argument index, or null to return the first argument.
     *
     * @return StubInterface This stub.
     */
    public function returnsArgument($index = null);

    /**
     * Invoke the stub.
     *
     * @param mixed $arguments,...
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the stub throws an exception.
     */
    public function __invoke();
}
