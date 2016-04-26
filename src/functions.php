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

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Matcher\Matcher;
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
 * Create a new mock builder.
 *
 * Each value in `$types` can be either a class name, or an ad hoc mock
 * definition. If only a single type is being mocked, the class name or
 * definition can be passed without being wrapped in an array.
 *
 * @param mixed $types The types to mock.
 *
 * @return MockBuilder The mock builder.
 */
function mockBuilder($types = array())
{
    return FacadeDriver::instance()->mockBuilderFactory->create($types);
}

/**
 * Create a new full mock, and return a stubbing handle.
 *
 * Each value in `$types` can be either a class name, or an ad hoc mock
 * definition. If only a single type is being mocked, the class name or
 * definition can be passed without being wrapped in an array.
 *
 * @param mixed $types The types to mock.
 *
 * @return InstanceStubbingHandle A stubbing handle around the new mock.
 */
function mock($types = array())
{
    $driver = FacadeDriver::instance();

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
 * @param mixed                $types     The types to mock.
 * @param Arguments|array|null $arguments The constructor arguments, or null to bypass the constructor.
 *
 * @return InstanceStubbingHandle A stubbing handle around the new mock.
 */
function partialMock($types = array(), $arguments = array())
{
    $driver = FacadeDriver::instance();

    return $driver->handleFactory->createStubbing(
        $driver->mockBuilderFactory->create($types)->partialWith($arguments)
    );
}

/**
 * Create a new stubbing handle.
 *
 * @param Mock|InstanceHandle $mock The mock.
 *
 * @return InstanceStubbingHandle The newly created handle.
 * @throws MockException          If the supplied mock is invalid.
 */
function on($mock)
{
    return FacadeDriver::instance()->handleFactory->createStubbing($mock);
}

/**
 * Create a new verification handle.
 *
 * @param Mock|InstanceHandle $mock The mock.
 *
 * @return InstanceVerificationHandle The newly created handle.
 * @throws MockException              If the supplied mock is invalid.
 */
function verify($mock)
{
    return FacadeDriver::instance()->handleFactory->createVerification($mock);
}

/**
 * Create a new static stubbing handle.
 *
 * @param Mock|Handle|ReflectionClass|string $class The class.
 *
 * @return StaticStubbingHandle The newly created handle.
 * @throws MockException        If the supplied class name is not a mock class.
 */
function onStatic($class)
{
    return
        FacadeDriver::instance()->handleFactory->createStubbingStatic($class);
}

/**
 * Create a new static verification handle.
 *
 * @param Mock|Handle|ReflectionClass|string $class The class.
 *
 * @return StaticVerificationHandle The newly created handle.
 * @throws MockException            If the supplied class name is not a mock class.
 */
function verifyStatic($class)
{
    return FacadeDriver::instance()->handleFactory
        ->createVerificationStatic($class);
}

/**
 * Create a new spy.
 *
 * @param callable|null $callback The callback, or null to create an anonymous spy.
 *
 * @return SpyVerifier The new spy.
 */
function spy($callback = null)
{
    return FacadeDriver::instance()->spyVerifierFactory
        ->createFromCallback($callback);
}

/**
 * Create a new stub.
 *
 * @param callable|null $callback The callback, or null to create an anonymous stub.
 *
 * @return StubVerifier The new stub.
 */
function stub($callback = null)
{
    return FacadeDriver::instance()->stubVerifierFactory
        ->createFromCallback($callback);
}

/**
 * Checks if the supplied events happened in chronological order.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return EventCollection|null The result.
 */
function checkInOrder()
{
    return FacadeDriver::instance()->eventOrderVerifier
        ->checkInOrderSequence(func_get_args());
}

/**
 * Throws an exception unless the supplied events happened in chronological
 * order.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return EventCollection The result.
 * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
 */
function inOrder()
{
    return FacadeDriver::instance()->eventOrderVerifier
        ->inOrderSequence(func_get_args());
}

/**
 * Checks if the supplied event sequence happened in chronological order.
 *
 * @param mixed<Event|EventCollection> $events The event sequence.
 *
 * @return EventCollection|null The result.
 */
function checkInOrderSequence($events)
{
    return FacadeDriver::instance()->eventOrderVerifier
        ->checkInOrderSequence($events);
}

/**
 * Throws an exception unless the supplied event sequence happened in
 * chronological order.
 *
 * @param mixed<Event|EventCollection> $events The event sequence.
 *
 * @return EventCollection The result.
 * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
 */
function inOrderSequence($events)
{
    return
        FacadeDriver::instance()->eventOrderVerifier->inOrderSequence($events);
}

/**
 * Checks that at least one event is supplied.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return EventCollection|null     The result.
 * @throws InvalidArgumentException If invalid input is supplied.
 */
function checkAnyOrder()
{
    return FacadeDriver::instance()->eventOrderVerifier
        ->checkAnyOrderSequence(func_get_args());
}

/**
 * Throws an exception unless at least one event is supplied.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return EventCollection          The result.
 * @throws InvalidArgumentException If invalid input is supplied.
 * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
 */
function anyOrder()
{
    return FacadeDriver::instance()->eventOrderVerifier
        ->anyOrderSequence(func_get_args());
}

/**
 * Checks if the supplied event sequence contains at least one event.
 *
 * @param mixed<Event|EventCollection> $events The event sequence.
 *
 * @return EventCollection|null     The result.
 * @throws InvalidArgumentException If invalid input is supplied.
 */
function checkAnyOrderSequence($events)
{
    return FacadeDriver::instance()->eventOrderVerifier
        ->checkAnyOrderSequence($events);
}

/**
 * Throws an exception unless the supplied event sequence contains at least
 * one event.
 *
 * @param mixed<Event|EventCollection> $events The event sequence.
 *
 * @return EventCollection          The result.
 * @throws InvalidArgumentException If invalid input is supplied.
 * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
 */
function anyOrderSequence($events)
{
    return
        FacadeDriver::instance()->eventOrderVerifier->anyOrderSequence($events);
}

/**
 * Create a new matcher that matches anything.
 *
 * @return Matcher The newly created matcher.
 */
function any()
{
    return FacadeDriver::instance()->matcherFactory->any();
}

/**
 * Create a new equal to matcher.
 *
 * @param mixed $value The value to check.
 *
 * @return Matcher The newly created matcher.
 */
function equalTo($value)
{
    return FacadeDriver::instance()->matcherFactory->equalTo($value);
}

/**
 * Create a new matcher that matches multiple arguments.
 *
 * @param mixed        $value            The value to check for each argument.
 * @param integer      $minimumArguments The minimum number of arguments.
 * @param integer|null $maximumArguments The maximum number of arguments.
 *
 * @return WildcardMatcher The newly created wildcard matcher.
 */
function wildcard(
    $value = null,
    $minimumArguments = 0,
    $maximumArguments = null
) {
    return FacadeDriver::instance()->matcherFactory
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
function setExportDepth($depth)
{
    return FacadeDriver::instance()->exporter->setDepth($depth);
}
