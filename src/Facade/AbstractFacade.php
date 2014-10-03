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

use Eloquent\Phony\Assertion\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Clock\ClockInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Factory\SpyFactoryInterface;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface;
use Eloquent\Phony\Spy\SpyVerifierInterface;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * An abstract base class for implementing facades.
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
        return static::spyVerifierFactory()->createFromCallback($callback);
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
        return static::stubVerifierFactory()
            ->createFromCallback($callback, $thisValue);
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
        return static::service(
            'Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface',
            function () {
                return new SpyVerifierFactory(
                    static::spyFactory(),
                    static::matcherFactory(),
                    static::matcherVerifier(),
                    static::callVerifierFactory(),
                    static::assertionRecorder(),
                    static::assertionRenderer()
                );
            }
        );
    }

    /**
     * Get the static stub verifier factory.
     *
     * @internal
     *
     * @return StubVerifierFactoryInterface The stub verifier factory.
     */
    protected static function stubVerifierFactory()
    {
        return static::service(
            'Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface',
            function () {
                return new StubVerifierFactory(
                    static::stubFactory(),
                    static::spyFactory(),
                    static::matcherFactory(),
                    static::matcherVerifier(),
                    static::callVerifierFactory(),
                    static::assertionRecorder(),
                    static::assertionRenderer()
                );
            }
        );
    }

    /**
     * Get the static spy factory.
     *
     * @internal
     *
     * @return SpyFactoryInterface The spy factory.
     */
    protected static function spyFactory()
    {
        return static::service(
            'Eloquent\Phony\Spy\Factory\SpyFactoryInterface',
            function () {
                return new SpyFactory(static::callFactory());
            }
        );
    }

    /**
     * Get the static stub factory.
     *
     * @internal
     *
     * @return StubFactoryInterface The stub factory.
     */
    protected static function stubFactory()
    {
        return static::service(
            'Eloquent\Phony\Stub\Factory\StubFactoryInterface',
            function () {
                return new StubFactory(
                    static::matcherFactory(),
                    static::matcherVerifier()
                );
            }
        );
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
        return static::service(
            'Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface',
            array(
                'Eloquent\Phony\Matcher\Factory\MatcherFactory',
                'instance',
            )
        );
    }

    /**
     * Get the static matcher verifier.
     *
     * @internal
     *
     * @return MatcherVerifierInterface The matcher verifier.
     */
    protected static function matcherVerifier()
    {
        return static::service(
            'Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface',
            array(
                'Eloquent\Phony\Matcher\Verification\MatcherVerifier',
                'instance',
            )
        );
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
        return static::service(
            'Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface',
            function () {
                return new CallVerifierFactory(
                    static::matcherFactory(),
                    static::matcherVerifier(),
                    static::assertionRecorder(),
                    static::assertionRenderer()
                );
            }
        );
    }

    /**
     * Get the static assertion recorder.
     *
     * @internal
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    protected static function assertionRecorder()
    {
        return static::service(
            'Eloquent\Phony\Assertion\AssertionRecorderInterface',
            array('Eloquent\Phony\Assertion\AssertionRecorder', 'instance')
        );
    }

    /**
     * Get the static assertion renderer.
     *
     * @internal
     *
     * @return AssertionRendererInterface The assertion renderer.
     */
    protected static function assertionRenderer()
    {
        return static::service(
            'Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface',
            array(
                'Eloquent\Phony\Assertion\Renderer\AssertionRenderer',
                'instance',
            )
        );
    }

    /**
     * Get the static call factory.
     *
     * @internal
     *
     * @return CallFactoryInterface The call factory.
     */
    protected static function callFactory()
    {
        return static::service(
            'Eloquent\Phony\Call\Factory\CallFactoryInterface',
            function () {
                return new CallFactory(static::sequencer(), static::clock());
            }
        );
    }

    /**
     * Get the static sequencer.
     *
     * @internal
     *
     * @return SequencerInterface The sequencer.
     */
    protected static function sequencer()
    {
        return static::service(
            'Eloquent\Phony\Sequencer\SequencerInterface',
            array('Eloquent\Phony\Sequencer\Sequencer', 'instance')
        );
    }

    /**
     * Get the static clock.
     *
     * @internal
     *
     * @return ClockInterface The clock.
     */
    protected static function clock()
    {
        return static::service(
            'Eloquent\Phony\Clock\ClockInterface',
            array('Eloquent\Phony\Clock\SystemClock', 'instance')
        );
    }

    /**
     * Get a service for the current facade class.
     *
     * @param string   $name    The name.
     * @param callable $factory The factory callback to create the service if it does not exist.
     *
     * @return mixed The service.
     */
    protected static function service($name, $factory)
    {
        $class = get_called_class();

        if (null === self::$container) {
            self::$container = array();
        }

        if (!array_key_exists($class, self::$container)) {
            self::$container[$class] = array();
        }

        if (!array_key_exists($name, self::$container[$class])) {
            self::$container[$class][$name] = $factory();
        }

        return self::$container[$class][$name];
    }

    private static $container;
}
