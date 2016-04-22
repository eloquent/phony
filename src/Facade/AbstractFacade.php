<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Mock\Handle\Handle;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\Stubbing\InstanceStubbingHandle;
use Eloquent\Phony\Mock\Handle\Stubbing\StaticStubbingHandle;
use Eloquent\Phony\Mock\Handle\Verification\InstanceVerificationHandle;
use Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandle;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Stub\StubVerifier;
use Exception;
use InvalidArgumentException;
use ReflectionClass;

/**
 * INTERNAL USE ONLY. An abstract base class for implementing facades.
 *
 * @api
 */
abstract class AbstractFacade
{
    /**
     * Create a new mock builder.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * @api
     *
     * @param mixed $types The types to mock.
     *
     * @return MockBuilder The mock builder.
     */
    public static function mockBuilder($types = array())
    {
        return static::driver()->mockBuilderFactory->create($types);
    }

    /**
     * Create a new full mock, and return a stubbing handle.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * @api
     *
     * @param mixed $types The types to mock.
     *
     * @return InstanceStubbingHandle A stubbing handle around the new mock.
     */
    public static function mock($types = array())
    {
        $driver = static::driver();

        return $driver->handleFactory->createStubbing(
            $driver->mockBuilderFactory->create($types)->full()
        );
    }

    /**
     * Create a new partial mock, and return a stubbing handle.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * Omitting `$arguments` will cause the original constructor to be called
     * with an empty argument list. However, if a `null` value is supplied for
     * `$arguments`, the original constructor will not be called at all.
     *
     * @api
     *
     * @param mixed                $types     The types to mock.
     * @param Arguments|array|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return InstanceStubbingHandle A stubbing handle around the new mock.
     */
    public static function partialMock($types = array(), $arguments = array())
    {
        $driver = static::driver();

        return $driver->handleFactory->createStubbing(
            $driver->mockBuilderFactory->create($types)->partialWith($arguments)
        );
    }

    /**
     * Create a new stubbing handle.
     *
     * @api
     *
     * @param Mock|InstanceHandle $mock The mock.
     *
     * @return InstanceStubbingHandle The newly created handle.
     * @throws MockException          If the supplied mock is invalid.
     */
    public static function on($mock)
    {
        return static::driver()->handleFactory->createStubbing($mock);
    }

    /**
     * Create a new verification handle.
     *
     * @api
     *
     * @param Mock|InstanceHandle $mock The mock.
     *
     * @return InstanceVerificationHandle The newly created handle.
     * @throws MockException              If the supplied mock is invalid.
     */
    public static function verify($mock)
    {
        return static::driver()->handleFactory->createVerification($mock);
    }

    /**
     * Create a new static stubbing handle.
     *
     * @api
     *
     * @param Mock|Handle|ReflectionClass|string $class The class.
     *
     * @return StaticStubbingHandle The newly created handle.
     * @throws MockException        If the supplied class name is not a mock class.
     */
    public static function onStatic($class)
    {
        return static::driver()->handleFactory->createStubbingStatic($class);
    }

    /**
     * Create a new static verification handle.
     *
     * @api
     *
     * @param Mock|Handle|ReflectionClass|string $class The class.
     *
     * @return StaticVerificationHandle The newly created handle.
     * @throws MockException            If the supplied class name is not a mock class.
     */
    public static function verifyStatic($class)
    {
        return
            static::driver()->handleFactory->createVerificationStatic($class);
    }

    /**
     * Create a new spy.
     *
     * @api
     *
     * @param callable|null $callback The callback, or null to create an anonymous spy.
     *
     * @return SpyVerifier The new spy.
     */
    public static function spy($callback = null)
    {
        return
            static::driver()->spyVerifierFactory->createFromCallback($callback);
    }

    /**
     * Create a new stub.
     *
     * @api
     *
     * @param callable|null $callback The callback, or null to create an anonymous stub.
     *
     * @return StubVerifier The new stub.
     */
    public static function stub($callback = null)
    {
        return static::driver()->stubVerifierFactory
            ->createFromCallback($callback);
    }

    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @api
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection|null The result.
     */
    public static function checkInOrder()
    {
        return static::driver()->eventOrderVerifier
            ->checkInOrderSequence(func_get_args());
    }

    /**
     * Throws an exception unless the supplied events happened in chronological
     * order.
     *
     * @api
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function inOrder()
    {
        return static::driver()->eventOrderVerifier
            ->inOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence happened in chronological order.
     *
     * @api
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection|null The result.
     */
    public static function checkInOrderSequence($events)
    {
        return
            static::driver()->eventOrderVerifier->checkInOrderSequence($events);
    }

    /**
     * Throws an exception unless the supplied event sequence happened in
     * chronological order.
     *
     * @api
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function inOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier->inOrderSequence($events);
    }

    /**
     * Checks that at least one event is supplied.
     *
     * @api
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public static function checkAnyOrder()
    {
        return static::driver()->eventOrderVerifier
            ->checkAnyOrderSequence(func_get_args());
    }

    /**
     * Throws an exception unless at least one event is supplied.
     *
     * @api
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function anyOrder()
    {
        return static::driver()->eventOrderVerifier
            ->anyOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence contains at least one event.
     *
     * @api
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public static function checkAnyOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier
            ->checkAnyOrderSequence($events);
    }

    /**
     * Throws an exception unless the supplied event sequence contains at least
     * one event.
     *
     * @api
     *
     * @param mixed<Event|EventCollection> $events The event sequence.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function anyOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier->anyOrderSequence($events);
    }

    /**
     * Create a new matcher that matches anything.
     *
     * @api
     *
     * @return Matcher The newly created matcher.
     */
    public static function any()
    {
        return static::driver()->matcherFactory->any();
    }

    /**
     * Create a new equal to matcher.
     *
     * @api
     *
     * @param mixed $value The value to check.
     *
     * @return Matcher The newly created matcher.
     */
    public static function equalTo($value)
    {
        return static::driver()->matcherFactory->equalTo($value);
    }

    /**
     * Create a new matcher that matches multiple arguments.
     *
     * @api
     *
     * @param mixed        $value            The value to check for each argument.
     * @param integer      $minimumArguments The minimum number of arguments.
     * @param integer|null $maximumArguments The maximum number of arguments.
     *
     * @return WildcardMatcher The newly created wildcard matcher.
     */
    public static function wildcard(
        $value = null,
        $minimumArguments = 0,
        $maximumArguments = null
    ) {
        return static::driver()->matcherFactory
            ->wildcard($value, $minimumArguments, $maximumArguments);
    }

    /**
     * Set the default export depth.
     *
     * Negative depths are treated as infinite depth.
     *
     * @api
     *
     * @param integer $depth The depth.
     *
     * @return integer The previous depth.
     */
    public static function setExportDepth($depth)
    {
        return static::driver()->exporter->setDepth($depth);
    }
}
