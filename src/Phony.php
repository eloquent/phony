<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony;

use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface;

/**
 * A facade for standalone Phony usage.
 */
class Phony
{
    /**
     * Create a new spy verifier for the supplied callback.
     *
     * @param callable|null $callback The callback, or null to create an unbound spy verifier.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public static function spy($callback = null)
    {
        return static::spyVerifierFactory()->createFromCallback($callback);
    }

    /**
     * Get the static spy verifier factory.
     *
     * @internal
     *
     * @return SpyVerifierFactoryInterface The spy verifier factory.
     */
    protected static function spyVerifierFactory()
    {
        return SpyVerifierFactory::instance();
    }
}
