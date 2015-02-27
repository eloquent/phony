<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Pho;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Integration\Pho\PhoFacadeDriver;
use Eloquent\Phony\Matcher\MatcherInterface;
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
 * Create a new mock builder.
 *
 * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface>|null $types        The types to mock.
 * @param array|object|null                       $definition The definition.
 * @param string|null                             $className  The class name.
 *
 * @return MockBuilderInterface The mock builder.
 */
function mockBuilder($types = null, $definition = null, $className = null)
{
    return PhoFacadeDriver::instance()->mockBuilderFactory()
        ->create($types, $definition, $className);
}

/**
 * Create a new mock.
 *
 * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface>|null $types        The types to mock.
 * @param ArgumentsInterface|array<integer,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
 * @param array|object|null                       $definition The definition.
 * @param string|null                             $className  The class name.
 *
 * @return InstanceStubbingProxyInterface A stubbing proxy around the new mock.
 */
function mock(
    $types = null,
    $arguments = null,
    $definition = null,
    $className = null
) {
    if (func_num_args() > 1) {
        $mock = PhoFacadeDriver::instance()->mockBuilderFactory()
            ->createMock($types, $arguments, $definition, $className);
    } else {
        $mock = PhoFacadeDriver::instance()->mockBuilderFactory()
            ->createMock($types);
    }

    return on($mock);
}

/**
 * Create a new full mock.
 *
 * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface>|null $types        The types to mock.
 * @param array|object|null                       $definition The definition.
 * @param string|null                             $className  The class name.
 *
 * @return InstanceStubbingProxyInterface A stubbing proxy around the new mock.
 */
function fullMock($types = null, $definition = null, $className = null)
{
    return on(
        PhoFacadeDriver::instance()->mockBuilderFactory()
            ->createFullMock($types, $definition, $className)
    );
}

/**
 * Create a new stubbing proxy.
 *
 * @param MockInterface|InstanceProxyInterface $mock The mock.
 *
 * @return InstanceStubbingProxyInterface The newly created proxy.
 * @throws MockExceptionInterface         If the supplied mock is invalid.
 */
function on($mock)
{
    return PhoFacadeDriver::instance()->proxyFactory()->createStubbing($mock);
}

/**
 * Create a new verification proxy.
 *
 * @param MockInterface|ProxyInterface|ReflectionClass|string $class The class.
 *
 * @return InstanceVerificationProxyInterface The newly created proxy.
 * @throws MockExceptionInterface             If the supplied mock is invalid.
 */
function verify($mock)
{
    return PhoFacadeDriver::instance()->proxyFactory()
        ->createVerification($mock);
}

/**
 * Create a new static stubbing proxy.
 *
 * @param ProxyInterface|ReflectionClass|object|string $class The class.
 *
 * @return StaticStubbingProxyInterface The newly created proxy.
 * @throws MockExceptionInterface       If the supplied class name is not a mock class.
 */
function onStatic($class)
{
    return PhoFacadeDriver::instance()->proxyFactory()
        ->createStubbingStatic($class);
}

/**
 * Create a new static verification proxy.
 *
 * @param MockInterface|ProxyInterface|ReflectionClass|string $class The class.
 *
 * @return StaticVerificationProxyInterface The newly created proxy.
 * @throws MockExceptionInterface           If the supplied class name is not a mock class.
 */
function verifyStatic($class)
{
    return PhoFacadeDriver::instance()->proxyFactory()
        ->createVerificationStatic($class);
}

/**
 * Create a new spy verifier for the supplied callback.
 *
 * @param callable|null $callback The callback, or null to create an unbound spy verifier.
 * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
 * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
 *
 * @return SpyVerifierInterface The newly created spy verifier.
 */
function spy(
    $callback = null,
    $useGeneratorSpies = null,
    $useTraversableSpies = null
) {
    return PhoFacadeDriver::instance()->spyVerifierFactory()
        ->createFromCallback(
            $callback,
            $useGeneratorSpies,
            $useTraversableSpies
        );
}

/**
 * Create a new stub verifier for the supplied callback.
 *
 * @param callable|null $callback  The callback, or null to create an unbound stub verifier.
 * @param object|null   $thisValue The $this value.
 * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
 * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
 *
 * @return StubVerifierInterface The newly created stub verifier.
 */
function stub(
    $callback = null,
    $thisValue = null,
    $useGeneratorSpies = null,
    $useTraversableSpies = null
) {
    return PhoFacadeDriver::instance()->stubVerifierFactory()
        ->createFromCallback(
            $callback,
            $thisValue,
            $useGeneratorSpies,
            $useTraversableSpies
        );
}

/**
 * Checks if the supplied events happened in chronological order.
 *
 * @param CallEventCollectionInterface $events,... The events.
 *
 * @return CallEventCollectionInterface|null The result.
 */
function checkInOrder()
{
    return PhoFacadeDriver::instance()->eventOrderVerifier()
        ->checkInOrderSequence(func_get_args());
}

/**
 * Throws an exception unless the supplied events happened in chronological
 * order.
 *
 * @param CallEventCollectionInterface $events,... The events.
 *
 * @return CallEventCollectionInterface The result.
 * @throws Exception If the assertion fails, and the assertion recorder throws exceptions.
 */
function inOrder()
{
    return PhoFacadeDriver::instance()->eventOrderVerifier()
        ->inOrderSequence(func_get_args());
}

/**
 * Checks if the supplied event sequence happened in chronological order.
 *
 * @param mixed<CallEventCollectionInterface> $events The event sequence.
 *
 * @return CallEventCollectionInterface|null The result.
 */
function checkInOrderSequence($events)
{
    return PhoFacadeDriver::instance()->eventOrderVerifier()
        ->checkInOrderSequence($events);
}

/**
 * Throws an exception unless the supplied event sequence happened in
 * chronological order.
 *
 * @param mixed<CallEventCollectionInterface> $events The event sequence.
 *
 * @return CallEventCollectionInterface The result.
 * @throws Exception If the assertion fails, and the assertion recorder throws exceptions.
 */
function inOrderSequence($events)
{
    return PhoFacadeDriver::instance()->eventOrderVerifier()
        ->inOrderSequence($events);
}

/**
 * Create a new matcher that matches anything.
 *
 * @return MatcherInterface The newly created matcher.
 */
function any()
{
    return PhoFacadeDriver::instance()->matcherFactory()->any();
}

/**
 * Create a new equal to matcher.
 *
 * @param mixed $value The value to check.
 *
 * @return MatcherInterface The newly created matcher.
 */
function equalTo($value)
{
    return PhoFacadeDriver::instance()->matcherFactory()->equalTo($value);
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
    return PhoFacadeDriver::instance()->matcherFactory()
        ->wildcard($value, $minimumArguments, $maximumArguments);
}
