<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\InstanceProxyInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\InstanceStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Verification\InstanceVerificationProxyInterface;
use Eloquent\Phony\Mock\Proxy\Verification\StaticVerificationProxyInterface;
use Eloquent\Phony\Spy\SpyVerifierInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
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
     * @return MockBuilderInterface The mock builder.
     */
    public static function mockBuilder($types = array())
    {
        return static::driver()->mockBuilderFactory()->create($types);
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
     * @return InstanceStubbingProxyInterface A stubbing handle around the new mock.
     */
    public static function mock($types = array())
    {
        return static::on(
            static::driver()->mockBuilderFactory()->createFullMock($types)
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
     * @param mixed                         $types     The types to mock.
     * @param ArgumentsInterface|array|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return InstanceStubbingProxyInterface A stubbing handle around the new mock.
     */
    public static function partialMock($types = array(), $arguments = array())
    {
        return static::on(
            static::driver()->mockBuilderFactory()
                ->createPartialMock($types, $arguments)
        );
    }

    /**
     * Create a new stubbing proxy.
     *
     * @api
     *
     * @param MockInterface|InstanceProxyInterface $mock The mock.
     *
     * @return InstanceStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface         If the supplied mock is invalid.
     */
    public static function on($mock)
    {
        return static::driver()->proxyFactory()->createStubbing($mock);
    }

    /**
     * Create a new verification proxy.
     *
     * @api
     *
     * @param MockInterface|InstanceProxyInterface $mock The mock.
     *
     * @return InstanceVerificationProxyInterface The newly created proxy.
     * @throws MockExceptionInterface             If the supplied mock is invalid.
     */
    public static function verify($mock)
    {
        return static::driver()->proxyFactory()->createVerification($mock);
    }

    /**
     * Create a new static stubbing proxy.
     *
     * @api
     *
     * @param MockInterface|ProxyInterface|ReflectionClass|string $class The class.
     *
     * @return StaticStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface       If the supplied class name is not a mock class.
     */
    public static function onStatic($class)
    {
        return static::driver()->proxyFactory()->createStubbingStatic($class);
    }

    /**
     * Create a new static verification proxy.
     *
     * @api
     *
     * @param MockInterface|ProxyInterface|ReflectionClass|string $class The class.
     *
     * @return StaticVerificationProxyInterface The newly created proxy.
     * @throws MockExceptionInterface           If the supplied class name is not a mock class.
     */
    public static function verifyStatic($class)
    {
        return static::driver()->proxyFactory()
            ->createVerificationStatic($class);
    }

    /**
     * Create a new spy.
     *
     * @api
     *
     * @param callable|null $callback The callback, or null to create an anonymous spy.
     *
     * @return SpyVerifierInterface The new spy.
     */
    public static function spy($callback = null)
    {
        return static::driver()->spyVerifierFactory()
            ->createFromCallback($callback);
    }

    /**
     * Create a new stub.
     *
     * @api
     *
     * @param callable|null $callback The callback, or null to create an anonymous stub.
     *
     * @return StubVerifierInterface The new stub.
     */
    public static function stub($callback = null)
    {
        return static::driver()->stubVerifierFactory()
            ->createFromCallback($callback);
    }

    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @api
     *
     * @param EventCollectionInterface ...$events The events.
     *
     * @return EventCollectionInterface|null The result.
     */
    public static function checkInOrder()
    {
        return static::driver()->eventOrderVerifier()
            ->checkInOrderSequence(func_get_args());
    }

    /**
     * Throws an exception unless the supplied events happened in chronological
     * order.
     *
     * @api
     *
     * @param EventCollectionInterface ...$events The events.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function inOrder()
    {
        return static::driver()->eventOrderVerifier()
            ->inOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence happened in chronological order.
     *
     * @api
     *
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @return EventCollectionInterface|null The result.
     */
    public static function checkInOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier()
            ->checkInOrderSequence($events);
    }

    /**
     * Throws an exception unless the supplied event sequence happened in
     * chronological order.
     *
     * @api
     *
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function inOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier()->inOrderSequence($events);
    }

    /**
     * Checks that at least one event is supplied.
     *
     * @api
     *
     * @param EventCollectionInterface ...$events The events.
     *
     * @return EventCollectionInterface|null The result.
     * @throws InvalidArgumentException      If invalid input is supplied.
     */
    public static function checkAnyOrder()
    {
        return static::driver()->eventOrderVerifier()
            ->checkAnyOrderSequence(func_get_args());
    }

    /**
     * Throws an exception unless at least one event is supplied.
     *
     * @api
     *
     * @param EventCollectionInterface ...$events The events.
     *
     * @return EventCollectionInterface The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function anyOrder()
    {
        return static::driver()->eventOrderVerifier()
            ->anyOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence contains at least one event.
     *
     * @api
     *
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @return EventCollectionInterface|null The result.
     * @throws InvalidArgumentException      If invalid input is supplied.
     */
    public static function checkAnyOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier()
            ->checkAnyOrderSequence($events);
    }

    /**
     * Throws an exception unless the supplied event sequence contains at least
     * one event.
     *
     * @api
     *
     * @param mixed<EventCollectionInterface> $events The event sequence.
     *
     * @return EventCollectionInterface The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function anyOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier()
            ->anyOrderSequence($events);
    }

    /**
     * Create a new matcher that matches anything.
     *
     * @api
     *
     * @return MatcherInterface The newly created matcher.
     */
    public static function any()
    {
        return static::driver()->matcherFactory()->any();
    }

    /**
     * Create a new equal to matcher.
     *
     * @api
     *
     * @param mixed $value The value to check.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public static function equalTo($value)
    {
        return static::driver()->matcherFactory()->equalTo($value);
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
     * @return WildcardMatcherInterface The newly created wildcard matcher.
     */
    public static function wildcard(
        $value = null,
        $minimumArguments = 0,
        $maximumArguments = null
    ) {
        return static::driver()->matcherFactory()
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
        return static::driver()->exporter()->setDepth($depth);
    }
}
