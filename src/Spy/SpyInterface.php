<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\CallInterface;
use Exception;
use ReflectionFunctionAbstract;

/**
 * The interface implemented by spies.
 */
interface SpyInterface
{
    /**
     * Get the subject.
     *
     * @return callable The subject.
     */
    public function subject();

    /**
     * Get the reflector.
     *
     * @return ReflectionFunctionAbstract The reflector.
     */
    public function reflector();

    /**
     * Set the calls.
     *
     * @param array<CallInterface> $calls The calls.
     */
    public function setCalls(array $calls);

    /**
     * Add a call.
     *
     * @param CallInterface $call The call.
     */
    public function addCall(CallInterface $call);

    /**
     * Get the calls.
     *
     * @return array<CallInterface> The calls.
     */
    public function calls();

    /**
     * Record a call by invocation.
     *
     * This method supports reference parameters.
     *
     * @param array<integer,mixed> The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function invokeWith(array $arguments);

    /**
     * Record a call by invocation.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function invoke();

    /**
     * Record a call by invocation.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function __invoke();
}
