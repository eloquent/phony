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

use Eloquent\Phony\Matcher\WildcardMatcherInterface;
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
     * @param callable|null $callback            The callback, or null to create an unbound spy verifier.
     * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
     * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public static function spy(
        $callback = null,
        $useTraversableSpies = null,
        $useGeneratorSpies = null
    ) {
        return static::driver()->spyVerifierFactory()->createFromCallback(
            $callback,
            $useTraversableSpies,
            $useGeneratorSpies
        );
    }

    /**
     * Create a new stub verifier for the supplied callback.
     *
     * @param callable|null $callback            The callback, or null to create an unbound stub verifier.
     * @param object|null   $thisValue           The $this value.
     * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
     * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
     *
     * @return StubVerifierInterface The newly created stub verifier.
     */
    public static function stub(
        $callback = null,
        $thisValue = null,
        $useTraversableSpies = null,
        $useGeneratorSpies = null
    ) {
        return static::driver()->stubVerifierFactory()->createFromCallback(
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
    public static function wildcard(
        $value = null,
        $minimumArguments = null,
        $maximumArguments = null
    ) {
        return static::driver()->matcherFactory()
            ->wildcard($value, $minimumArguments, $maximumArguments);
    }
}
