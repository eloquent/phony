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
use Eloquent\Phony\Invocation\WrappedInvocableInterface;

/**
 * The interface implemented by spies.
 */
interface SpyInterface extends WrappedInvocableInterface
{
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
     * Get the recorded calls.
     *
     * @return array<CallInterface> The recorded calls.
     */
    public function recordedCalls();
}
