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

use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\InstanceStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxyInterface;
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
     * @param array<string|object>|string|object|null $types      The types to mock.
     * @param array|object|null                       $definition The definition.
     * @param string|null                             $className  The class name.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public static function mock(
        $types = null,
        $definition = null,
        $className = null
    ) {
        return static::driver()->mockBuilderFactory()
            ->create($types, $definition, $className);
    }

    /**
     * Create a new stubbing proxy.
     *
     * @param MockInterface $mock The mock.
     *
     * @return InstanceStubbingProxyInterface The stubbing proxy.
     */
    public static function on(MockInterface $mock)
    {
        return static::driver()->proxyFactory()->createStubbing($mock);
    }

    /**
     * Create a new static stubbing proxy.
     *
     * @param ReflectionClass|object|string $class The class.
     *
     * @return StaticStubbingProxyInterface The stubbing proxy.
     * @throws MockExceptionInterface       If the supplied class name is not a mock class.
     */
    public static function onStatic($class)
    {
        return static::driver()->proxyFactory()->createStubbingStatic($class);
    }

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
     * Checks if the supplied events happened in chronological order.
     *
     * @param CallEventCollectionInterface $events,... The events.
     *
     * @return CallEventCollectionInterface|null The result.
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
     * @param CallEventCollectionInterface $events,... The events.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public static function inOrder()
    {
        return static::driver()->eventOrderVerifier()
            ->inOrderSequence(func_get_args());
    }

    /**
     * Checks if the supplied event sequence happened in chronological order.
     *
     * @param mixed<CallEventCollectionInterface> $events The event sequence.
     *
     * @return CallEventCollectionInterface|null The result.
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
     * @param mixed<CallEventCollectionInterface> $events The event sequence.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public static function inOrderSequence($events)
    {
        return static::driver()->eventOrderVerifier()->inOrderSequence($events);
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
}
