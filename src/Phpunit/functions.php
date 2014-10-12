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
 * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
 * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
 *
 * @return SpyVerifierInterface The newly created spy verifier.
 */
function spy(
    $callback = null,
    $useTraversableSpies = null,
    $useGeneratorSpies = null
) {
    return PhpunitFacadeDriver::instance()->spyVerifierFactory()
        ->createFromCallback(
            $callback,
            $useTraversableSpies,
            $useGeneratorSpies
        );
}

/**
 * Create a new stub verifier for the supplied callback.
 *
 * @param callable|null $callback  The callback, or null to create an unbound stub verifier.
 * @param object|null   $thisValue The $this value.
 * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
 * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
 *
 * @return StubVerifierInterface The newly created stub verifier.
 */
function stub(
    $callback = null,
    $thisValue = null,
    $useTraversableSpies = null,
    $useGeneratorSpies = null
) {
    return PhpunitFacadeDriver::instance()->stubVerifierFactory()
        ->createFromCallback(
            $callback,
            $thisValue,
            $useTraversableSpies,
            $useGeneratorSpies
        );
}

/**
 * Create a new matcher that matches multiple arguments.
 *
 * @param mixed        $value            The value to check for each argument.
 * @param integer|null $minimumArguments The minimum number of arguments.
 * @param integer|null $maximumArguments The maximum number of arguments.
 *
 * @return WildcardMatcherInterface The newly created wildcard matcher.
 */
function wildcard(
    $value = null,
    $minimumArguments = null,
    $maximumArguments = null
) {
    return PhpunitFacadeDriver::instance()->matcherFactory()
        ->wildcard($value, $minimumArguments, $maximumArguments);
}
