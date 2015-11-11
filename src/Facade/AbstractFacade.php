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
use ReflectionClass;

/**
 * An abstract base class for implementing facades.
 *
 * @internal
 */
abstract class AbstractFacade
{
    /**
     * Create a new mock builder.
     *
     * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface>|null $types      The types to mock.
     * @param array|object|null                                                                                   $definition The definition.
     * @param string|null                                                                                         $className  The class name.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public static function mockBuilder(
        $types = null,
        $definition = null,
        $className = null
    ) {
        return static::driver()->mockBuilderFactory()
            ->create($types, $definition, $className);
    }

    /**
     * Create a new full mock.
     *
     * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface>|null $types      The types to mock.
     * @param array|object|null                                                                                   $definition The definition.
     * @param string|null                                                                                         $className  The class name.
     *
     * @return InstanceStubbingProxyInterface A stubbing proxy around the new mock.
     */
    public static function mock(
        $types = null,
        $definition = null,
        $className = null
    ) {
        return static::on(
            static::driver()->mockBuilderFactory()
                ->createFullMock($types, $definition, $className)
        );
    }

    /**
     * Create a new partial mock.
     *
     * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface>|null $types      The types to mock.
     * @param ArgumentsInterface|array|null                                                                       $arguments  The constructor arguments, or null to bypass the constructor.
     * @param array|object|null                                                                                   $definition The definition.
     * @param string|null                                                                                         $className  The class name.
     *
     * @return InstanceStubbingProxyInterface A stubbing proxy around the new mock.
     */
    public static function partialMock(
        $types = null,
        $arguments = null,
        $definition = null,
        $className = null
    ) {
        if (func_num_args() > 1) {
            $mock = static::driver()->mockBuilderFactory()->createPartialMock(
                $types,
                $arguments,
                $definition,
                $className
            );
        } else {
            $mock = static::driver()->mockBuilderFactory()
                ->createPartialMock($types);
        }

        return static::on($mock);
    }

    /**
     * Create a new stubbing proxy.
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
     * Create a new spy verifier for the supplied callback.
     *
     * @param callable|null $callback            The callback, or null to create an unbound spy verifier.
     * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
     * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public static function spy(
        $callback = null,
        $useGeneratorSpies = null,
        $useTraversableSpies = null
    ) {
        return static::driver()->spyVerifierFactory()->createFromCallback(
            $callback,
            $useGeneratorSpies,
            $useTraversableSpies
        );
    }

    /**
     * Create a new stub verifier for the supplied callback.
     *
     * @param callable|null $callback              The callback, or null to create an unbound stub verifier.
     * @param mixed         $self                  The self value.
     * @param callable|null $defaultAnswerCallback The callback to use when creating a default answer.
     * @param boolean|null  $useGeneratorSpies     True if generator spies should be used.
     * @param boolean|null  $useTraversableSpies   True if traversable spies should be used.
     *
     * @return StubVerifierInterface The newly created stub verifier.
     */
    public static function stub(
        $callback = null,
        $self = null,
        $defaultAnswerCallback = null,
        $useGeneratorSpies = null,
        $useTraversableSpies = null
    ) {
        return static::driver()->stubVerifierFactory()->createFromCallback(
            $callback,
            $self,
            $defaultAnswerCallback,
            $useGeneratorSpies,
            $useTraversableSpies
        );
    }

    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @param EventCollectionInterface $events,... The events.
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
     * @param EventCollectionInterface $events,... The events.
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
     * @param EventCollectionInterface $events,... The events.
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
     * @param EventCollectionInterface $events,... The events.
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
     * @return MatcherInterface The newly created matcher.
     */
    public static function any()
    {
        return static::driver()->matcherFactory()->any();
    }

    /**
     * Create a new equal to matcher.
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

    /**
     * Set the default export depth.
     *
     * Negative depths are treated as infinite depth.
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
