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
     * Create a new spy.
     *
     * @param callable|null $subject The subject, or null to create an unbound spy.
     *
     * @return SpyVerifierInterface The newly created spy.
     */
    public static function spy($subject = null)
    {
        return static::spyVerifierFactory()->createFromSubject($subject);
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
