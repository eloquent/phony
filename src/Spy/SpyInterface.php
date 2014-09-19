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
use Eloquent\Phony\Spy\Exception\UndefinedSubjectException;
use Exception;

/**
 * The interface implemented by spies.
 */
interface SpyInterface
{
    /**
     * Returns true if this spy has a subject.
     *
     * @return boolean True if this spy has a subject.
     */
    public function hasSubject();

    /**
     * Get the subject.
     *
     * @return callable                  The subject.
     * @throws UndefinedSubjectException If there is no subject.
     */
    public function subject();

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
     * @param mixed $arguments,...
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function __invoke();
}
