<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifierInterface;

/**
 * The interface implemented by call verifier factories.
 */
interface CallVerifierFactoryInterface
{
    /**
     * Wrap the supplied call in a verifier.
     *
     * @param CallInterface $call The call.
     *
     * @return CallVerifierInterface The call verifier.
     */
    public function fromCall(CallInterface $call);

    /**
     * Wrap the supplied calls in verifiers.
     *
     * @param array<CallInterface> $calls The calls.
     *
     * @return array<CallVerifierInterface> The call verifiers.
     */
    public function fromCalls(array $calls);
}
