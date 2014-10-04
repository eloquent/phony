<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Phpunit;

use Eloquent\Phony\Integration\Phpunit\PhpunitFacadeDriver;
use Eloquent\Phony\Spy\SpyVerifierInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * Create a new spy verifier for the supplied callback.
 *
 * @param callable|null $callback The callback, or null to create an unbound spy verifier.
 *
 * @return SpyVerifierInterface The newly created spy verifier.
 */
function spy($callback = null)
{
    return PhpunitFacadeDriver::instance()->spyVerifierFactory()
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
function stub($callback = null, $thisValue = null)
{
    return PhpunitFacadeDriver::instance()->stubVerifierFactory()
        ->createFromCallback($callback, $thisValue);
}
