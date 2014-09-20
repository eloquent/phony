<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifier;
use Eloquent\Phony\Call\CallVerifierInterface;

/**
 * Creates call verifiers.
 */
class CallVerifierFactory implements CallVerifierFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return CallVerifierFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Wrap the supplied call in a verifier, or return unchanged if already
     * wrapped.
     *
     * @param CallInterface|CallVerifierInterface $call The call.
     *
     * @return CallVerifierInterface The call verifier.
     */
    public function adapt($call)
    {
        if ($call instanceof CallVerifierInterface) {
            return $call;
        }

        return new CallVerifier($call);
    }

    /**
     * Wrap the supplied calls in verifiers, or return unchanged if already
     * wrapped.
     *
     * @param array<integer,CallInterface|CallVerifierInterface> $calls The calls.
     *
     * @return array<integer,CallVerifierInterface> The call verifiers.
     */
    public function adaptAll(array $calls)
    {
        $verifiers = array();
        foreach ($calls as $call) {
            if ($call instanceof CallVerifierInterface) {
                $verifiers[] = $call;
            } else {
                $verifiers[] = new CallVerifier($call);
            }
        }

        return $verifiers;
    }

    private static $instance;
}
