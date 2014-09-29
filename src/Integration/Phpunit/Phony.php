<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface;

/**
 * A facade for Phony usage under PHPUnit.
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
        if (null === self::$spyVerifierFactory) {
            self::$spyVerifierFactory = new SpyVerifierFactory(
                null,
                static::matcherFactory(),
                null,
                static::callVerifierFactory()
            );
        }

        return self::$spyVerifierFactory;
    }

    /**
     * Get the static call verifier factory.
     *
     * @internal
     *
     * @return CallVerifierFactoryInterface The call verifier factory.
     */
    protected static function callVerifierFactory()
    {
        if (null === self::$callVerifierFactory) {
            self::$callVerifierFactory = new CallVerifierFactory(
                static::matcherFactory(),
                null,
                PhpunitAssertionRecorder::instance()
            );
        }

        return self::$callVerifierFactory;
    }

    /**
     * Get the static matcher factory.
     *
     * @internal
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    protected static function matcherFactory()
    {
        if (null === self::$matcherFactory) {
            self::$matcherFactory =
                new MatcherFactory(array(PhpunitMatcherDriver::instance()));
        }

        return self::$matcherFactory;
    }

    private static $spyVerifierFactory;
    private static $callVerifierFactory;
    private static $matcherFactory;
}
