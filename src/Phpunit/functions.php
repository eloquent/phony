<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Phpunit;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Integration\Phpunit\PhpunitFacadeDriver;
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
use InvalidArgumentException;
use ReflectionClass;

/**
 * Create a new mock builder.
 *
 * The `$types` argument may be a class name, a reflection class, or a mock
 * builder. It may also be an array of any of these.
 *
 * If `$types` is omitted, or `null`, no existing type will be used when
 * generating the mock class. This is useful in the case of ad hoc mocks,
 * where mocks need not imitate an existing type.
 *
 * @api
 *
 * @param mixed             $types      The types to mock.
 * @param array|object|null $definition The definition.
 * @param string|null       $className  The class name.
 *
 * @return MockBuilderInterface The mock builder.
 */
function mockBuilder($types = null, $definition = null, $className = null)
{
    return PhpunitFacadeDriver::instance()->mockBuilderFactory()
        ->create($types, $definition, $className);
}

/**
 * Create a new full mock.
 *
 * The `$types` argument may be a class name, a reflection class, or a mock
 * builder. It may also be an array of any of these.
 *
 * If `$types` is omitted, or `null`, no existing type will be used when
 * generating the mock class. This is useful in the case of ad hoc mocks,
 * where mocks need not imitate an existing type.
 *
 * @api
 *
 * @param mixed             $types      The types to mock.
 * @param array|object|null $definition The definition.
 * @param string|null       $className  The class name.
 *
 * @return InstanceStubbingProxyInterface A stubbing proxy around the new mock.
 */
function mock($types = null, $definition = null, $className = null)
{
    return on(
        PhpunitFacadeDriver::instance()->mockBuilderFactory()
            ->createFullMock($types, $definition, $className)
    );
}

/**
 * Create a new partial mock.
 *
 * The `$types` argument may be a class name, a reflection class, or a mock
 * builder. It may also be an array of any of these.
 *
 * If `$types` is omitted, or `null`, no existing type will be used when
 * generating the mock class. This is useful in the case of ad hoc mocks,
 * where mocks need not imitate an existing type.
 *
 * @api
 *
 * @param mixed                         $types      The types to mock.
 * @param ArgumentsInterface|array|null $arguments  The constructor arguments, or null to bypass the constructor.
 * @param array|object|null             $definition The definition.
 * @param string|null                   $className  The class name.
 *
 * @return InstanceStubbingProxyInterface A stubbing proxy around the new mock.
 */
function partialMock(
    $types = null,
    $arguments = null,
    $definition = null,
    $className = null
) {
    if (func_num_args() > 1) {
        $mock = PhpunitFacadeDriver::instance()->mockBuilderFactory()
            ->createPartialMock($types, $arguments, $definition, $className);
    } else {
        $mock = PhpunitFacadeDriver::instance()->mockBuilderFactory()
            ->createPartialMock($types);
    }

    return on($mock);
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
function on($mock)
{
    return PhpunitFacadeDriver::instance()->proxyFactory()
        ->createStubbing($mock);
}

/**
 * Create a new verification proxy.
 *
 * @api
 *
 * @param MockInterface|ProxyInterface|ReflectionClass|string $class The class.
 *
 * @return InstanceVerificationProxyInterface The newly created proxy.
 * @throws MockExceptionInterface             If the supplied mock is invalid.
 */
function verify($mock)
{
    return PhpunitFacadeDriver::instance()->proxyFactory()
        ->createVerification($mock);
}

/**
 * Create a new static stubbing proxy.
 *
 * @api
 *
 * @param ProxyInterface|ReflectionClass|object|string $class The class.
 *
 * @return StaticStubbingProxyInterface The newly created proxy.
 * @throws MockExceptionInterface       If the supplied class name is not a mock class.
 */
function onStatic($class)
{
    return PhpunitFacadeDriver::instance()->proxyFactory()
        ->createStubbingStatic($class);
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
function verifyStatic($class)
{
    return PhpunitFacadeDriver::instance()->proxyFactory()
        ->createVerificationStatic($class);
}

/**
 * Create a new spy verifier for the supplied callback.
 *
 * @api
 *
 * @param callable|null $callback            The callback, or null to create an unbound spy verifier.
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
    return PhpunitFacadeDriver::instance()->spyVerifierFactory()
        ->createFromCallback(
            $callback,
            $useGeneratorSpies,
            $useTraversableSpies
        );
}

/**
 * Create a new stub verifier for the supplied callback.
 *
 * @api
 *
 * @param callable|null $callback              The callback, or null to create an unbound stub verifier.
 * @param object|null   $self                  The self value.
 * @param callable|null $defaultAnswerCallback The callback to use when creating a default answer.
 * @param boolean|null  $useGeneratorSpies     True if generator spies should be used.
 * @param boolean|null  $useTraversableSpies   True if traversable spies should be used.
 *
 * @return StubVerifierInterface The newly created stub verifier.
 */
function stub(
    $callback = null,
    $self = null,
    $defaultAnswerCallback = null,
    $useGeneratorSpies = null,
    $useTraversableSpies = null
) {
    return PhpunitFacadeDriver::instance()->stubVerifierFactory()
        ->createFromCallback(
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
 * @api
 *
 * @param EventCollectionInterface ...$events The events.
 *
 * @return EventCollectionInterface|null The result.
 */
function checkInOrder()
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
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
function inOrder()
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
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
function checkInOrderSequence($events)
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
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
function inOrderSequence($events)
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
        ->inOrderSequence($events);
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
function checkAnyOrder()
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
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
function anyOrder()
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
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
function checkAnyOrderSequence($events)
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
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
function anyOrderSequence($events)
{
    return PhpunitFacadeDriver::instance()->eventOrderVerifier()
        ->anyOrderSequence($events);
}

/**
 * Create a new matcher that matches anything.
 *
 * @api
 *
 * @return MatcherInterface The newly created matcher.
 */
function any()
{
    return PhpunitFacadeDriver::instance()->matcherFactory()->any();
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
function equalTo($value)
{
    return PhpunitFacadeDriver::instance()->matcherFactory()->equalTo($value);
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
function wildcard(
    $value = null,
    $minimumArguments = 0,
    $maximumArguments = null
) {
    return PhpunitFacadeDriver::instance()->matcherFactory()
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
function setExportDepth($depth)
{
    return PhpunitFacadeDriver::instance()->exporter()->setDepth($depth);
}
