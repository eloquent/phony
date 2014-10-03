<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Spy\SpyVerifierInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * An abstract base class for implementing facades.
 *
 * @internal
 */
abstract class AbstractFacade
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
        return static::driver()->spyVerifierFactory()
            ->createFromCallback($callback);
    }

    /**
     * Create a new stub verifier for the supplied callback.
     *
     * @param callable|null $callback  The callback, or null to create an unbound stub verifier.
     * @param object|null   $thisValue The $this value.
     *
     * @return StubVerifierInterface The newly created stub verifier.
     */
    public static function stub($callback = null, $thisValue = null)
    {
        return static::driver()->stubVerifierFactory()
            ->createFromCallback($callback, $thisValue);
    }
}
