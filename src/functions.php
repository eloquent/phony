<?php

declare(strict_types=1);

namespace Eloquent\Phony;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Facade\Globals;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Mock\Handle\Handle;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandle;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Stub\StubVerifier;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionType;
use Throwable;

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
function mockBuilder($types = []): MockBuilder
{
    return Globals::$container->mockBuilderFactory->create($types);
}

/**
 * Create a new full mock, and return a handle.
 *
 * Each value in `$types` can be either a class name, or an ad hoc mock
 * definition. If only a single type is being mocked, the class name or
 * definition can be passed without being wrapped in an array.
 *
 * @param mixed $types The types to mock.
 *
 * @return InstanceHandle A handle around the new mock.
 */
function mock($types = []): InstanceHandle
{
    $container = Globals::$container;

    return $container->handleFactory->instanceHandle(
        $container->mockBuilderFactory->create($types)->full()
    );
}

/**
 * Create a new partial mock, and return a handle.
 *
 * Each value in `$types` can be either a class name, or an ad hoc mock
 * definition. If only a single type is being mocked, the class name or
 * definition can be passed without being wrapped in an array.
 *
 * Omitting `$arguments` will cause the original constructor to be called
 * with an empty argument list. However, if a `null` value is supplied for
 * `$arguments`, the original constructor will not be called at all.
 *
 * @param mixed                           $types     The types to mock.
 * @param Arguments|array<int,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
 *
 * @return InstanceHandle A handle around the new mock.
 */
function partialMock($types = [], $arguments = []): InstanceHandle
{
    $container = Globals::$container;

    return $container->handleFactory->instanceHandle(
        $container->mockBuilderFactory->create($types)->partialWith($arguments)
    );
}

/**
 * Create a new handle.
 *
 * @param Mock|InstanceHandle $mock The mock.
 *
 * @return InstanceHandle The newly created handle.
 * @throws MockException  If the supplied mock is invalid.
 */
function on($mock): InstanceHandle
{
    return Globals::$container->handleFactory->instanceHandle($mock);
}

/**
 * Create a new static handle.
 *
 * @param Mock|Handle|ReflectionClass<object>|string $class The class.
 *
 * @return StaticHandle  The newly created handle.
 * @throws MockException If the supplied class name is not a mock class.
 */
function onStatic($class): StaticHandle
{
    return Globals::$container->handleFactory->staticHandle($class);
}

/**
 * Create a new spy.
 *
 * @param ?callable $callback The callback, or null to create an anonymous spy.
 *
 * @return SpyVerifier The new spy.
 */
function spy(callable $callback = null): SpyVerifier
{
    return Globals::$container->spyVerifierFactory
        ->createFromCallback($callback);
}

/**
 * Create a spy of a function in the global namespace, and declare it as a
 * function in another namespace.
 *
 * @param callable&string $function  The name of the function in the global namespace.
 * @param string          $namespace The namespace in which to create the new function.
 *
 * @return SpyVerifier The new spy.
 */
function spyGlobal(string $function, string $namespace): SpyVerifier
{
    return Globals::$container->spyVerifierFactory
        ->createGlobal($function, $namespace);
}

/**
 * Create a new stub.
 *
 * @param ?callable $callback The callback, or null to create an anonymous stub.
 *
 * @return StubVerifier The new stub.
 */
function stub(callable $callback = null): StubVerifier
{
    return Globals::$container->stubVerifierFactory
        ->createFromCallback($callback);
}

/**
 * Create a stub of a function in the global namespace, and declare it as a
 * function in another namespace.
 *
 * Stubs created via this function do not forward to the original function by
 * default. This differs from stubs created by other methods.
 *
 * @param callable&string $function  The name of the function in the global namespace.
 * @param string          $namespace The namespace in which to create the new function.
 *
 * @return StubVerifier The new stub.
 */
function stubGlobal(string $function, string $namespace): StubVerifier
{
    return Globals::$container->stubVerifierFactory
        ->createGlobal($function, $namespace);
}

/**
 * Restores the behavior of any functions in the global namespace that have been
 * altered via spyGlobal() or stubGlobal().
 */
function restoreGlobalFunctions(): void
{
    Globals::$container->functionHookManager->restoreGlobalFunctions();
}

/**
 * Checks if the supplied events happened in chronological order.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return ?EventCollection The result.
 */
function checkInOrder(object ...$events): ?EventCollection
{
    return Globals::$container->eventOrderVerifier->checkInOrder(...$events);
}

/**
 * Throws an exception unless the supplied events happened in chronological
 * order.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return EventCollection The result.
 * @throws Throwable       If the assertion fails.
 */
function inOrder(object ...$events): EventCollection
{
    /** @var EventCollection */
    $result = Globals::$container->eventOrderVerifier->inOrder(...$events);

    return $result;
}

/**
 * Checks that at least one event is supplied.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return ?EventCollection         The result.
 * @throws InvalidArgumentException If invalid input is supplied.
 */
function checkAnyOrder(object ...$events): ?EventCollection
{
    return Globals::$container->eventOrderVerifier->checkAnyOrder(...$events);
}

/**
 * Throws an exception unless at least one event is supplied.
 *
 * @param Event|EventCollection ...$events The events.
 *
 * @return EventCollection          The result.
 * @throws InvalidArgumentException If invalid input is supplied.
 * @throws Throwable                If the assertion fails.
 */
function anyOrder(object ...$events): EventCollection
{
    /** @var EventCollection */
    $result = Globals::$container->eventOrderVerifier->anyOrder(...$events);

    return $result;
}

/**
 * Create a new matcher that matches anything.
 *
 * @return Matcher The newly created matcher.
 */
function any(): Matcher
{
    return Globals::$container->matcherFactory->any();
}

/**
 * Create a new equal to matcher.
 *
 * @param mixed $value The value to check against.
 *
 * @return Matcher The newly created matcher.
 */
function equalTo($value): Matcher
{
    return Globals::$container->matcherFactory->equalTo($value, false);
}

/**
 * Create a new instance of matcher.
 *
 * @param string|object $type The type to check against.
 *
 * @return Matcher The newly created matcher.
 */
function anInstanceOf($type): Matcher
{
    return Globals::$container->matcherFactory->anInstanceOf($type);
}

/**
 * Create a new matcher that matches multiple arguments.
 *
 * Negative values for $maximumArguments are treated as "no maximum".
 *
 * @param mixed $value            The value to check for each argument.
 * @param int   $minimumArguments The minimum number of arguments.
 * @param int   $maximumArguments The maximum number of arguments.
 *
 * @return WildcardMatcher The newly created wildcard matcher.
 */
function wildcard(
    $value = null,
    int $minimumArguments = 0,
    int $maximumArguments = -1
): WildcardMatcher {
    return Globals::$container->matcherFactory
        ->wildcard($value, $minimumArguments, $maximumArguments);
}

/**
 * Get an "empty" value for the supplied type.
 *
 * @param ReflectionType $type The type.
 *
 * @return mixed An "empty" value of the supplied type.
 */
function emptyValue(ReflectionType $type)
{
    return Globals::$container->emptyValueFactory->fromType($type);
}

/**
 * Set the default export depth.
 *
 * Negative depths are treated as infinite depth.
 *
 * @param int $depth The depth.
 *
 * @return int The previous depth.
 */
function setExportDepth(int $depth): int
{
    return Globals::$container->exporter->setDepth($depth);
}

/**
 * Turn on or off the use of ANSI colored output.
 *
 * Pass `null` to detect automatically.
 *
 * @param ?bool $useColor True to use color.
 */
function setUseColor(?bool $useColor): void
{
    $container = Globals::$container;

    $container->assertionRenderer->setUseColor($useColor);
    $container->differenceEngine->setUseColor($useColor);
}
