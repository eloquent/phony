<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Handle\HandleInterface;
use Eloquent\Phony\Mock\Handle\InstanceHandleInterface;
use Eloquent\Phony\Mock\Handle\Stubbing\InstanceStubbingHandleInterface;
use Eloquent\Phony\Mock\Handle\Stubbing\StaticStubbingHandleInterface;
use Eloquent\Phony\Mock\Handle\Verification\InstanceVerificationHandleInterface;
use Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandleInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Spy\SpyVerifierInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use Exception;
use InvalidArgumentException;
use ReflectionClass;

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
function mockBuilder($types = array())
{
    return FacadeDriver::instance()->mockBuilderFactory()->create($types);
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
 * @return InstanceStubbingHandleInterface A stubbing handle around the new mock.
 */
function mock($types = array())
{
    return on(
        FacadeDriver::instance()->mockBuilderFactory()->createFullMock($types)
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
 * @return InstanceStubbingHandleInterface A stubbing handle around the new mock.
 */
function partialMock($types = array(), $arguments = array())
{
    return on(
        FacadeDriver::instance()->mockBuilderFactory()
            ->createPartialMock($types, $arguments)
    );
}

/**
 * Create a new stubbing handle.
 *
 * @api
 *
 * @param MockInterface|InstanceHandleInterface $mock The mock.
 *
 * @return InstanceStubbingHandleInterface The newly created handle.
 * @throws MockExceptionInterface          If the supplied mock is invalid.
 */
function on($mock)
{
    return FacadeDriver::instance()->handleFactory()->createStubbing($mock);
}

/**
 * Create a new verification handle.
 *
 * @api
 *
 * @param MockInterface|InstanceHandleInterface $mock The mock.
 *
 * @return InstanceVerificationHandleInterface The newly created handle.
 * @throws MockExceptionInterface              If the supplied mock is invalid.
 */
function verify($mock)
{
    return FacadeDriver::instance()->handleFactory()->createVerification($mock);
}

/**
 * Create a new static stubbing handle.
 *
 * @api
 *
 * @param MockInterface|HandleInterface|ReflectionClass|string $class The class.
 *
 * @return StaticStubbingHandleInterface The newly created handle.
 * @throws MockExceptionInterface        If the supplied class name is not a mock class.
 */
function onStatic($class)
{
    return FacadeDriver::instance()->handleFactory()
        ->createStubbingStatic($class);
}

/**
 * Create a new static verification handle.
 *
 * @api
 *
 * @param MockInterface|HandleInterface|ReflectionClass|string $class The class.
 *
 * @return StaticVerificationHandleInterface The newly created handle.
 * @throws MockExceptionInterface            If the supplied class name is not a mock class.
 */
function verifyStatic($class)
{
    return FacadeDriver::instance()->handleFactory()
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
function spy($callback = null)
{
    return FacadeDriver::instance()->spyVerifierFactory()
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
function stub($callback = null)
{
    return FacadeDriver::instance()->stubVerifierFactory()
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
function checkInOrder()
{
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->eventOrderVerifier()
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
    return FacadeDriver::instance()->matcherFactory()->any();
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
    return FacadeDriver::instance()->matcherFactory()->equalTo($value);
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
    return FacadeDriver::instance()->matcherFactory()
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
    return FacadeDriver::instance()->exporter()->setDepth($depth);
}
