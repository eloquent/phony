<?php

declare(strict_types=1);

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
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
 * Used for implementing facades.
 */
trait FacadeTrait
{
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
    public static function mockBuilder($types = []): MockBuilder
    {
        return static::driver()->mockBuilderFactory->create($types);
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
    public static function mock($types = []): InstanceHandle
    {
        $driver = static::driver();

        return $driver->handleFactory->instanceHandle(
            $driver->mockBuilderFactory->create($types)->full()
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
     * @param mixed                $types     The types to mock.
     * @param Arguments|array|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return InstanceHandle A handle around the new mock.
     */
    public static function partialMock(
        $types = [],
        $arguments = []
    ): InstanceHandle {
        $driver = static::driver();

        return $driver->handleFactory->instanceHandle(
            $driver->mockBuilderFactory->create($types)->partialWith($arguments)
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
    public static function on($mock): InstanceHandle
    {
        return static::driver()->handleFactory->instanceHandle($mock);
    }

    /**
     * Create a new static handle.
     *
     * @param Mock|Handle|ReflectionClass|string $class The class.
     *
     * @return StaticHandle  The newly created handle.
     * @throws MockException If the supplied class name is not a mock class.
     */
    public static function onStatic($class): StaticHandle
    {
        return static::driver()->handleFactory->staticHandle($class);
    }

    /**
     * Create a new spy.
     *
     * @param callable|null $callback The callback, or null to create an anonymous spy.
     *
     * @return SpyVerifier The new spy.
     */
    public static function spy(callable $callback = null): SpyVerifier
    {
        return
            static::driver()->spyVerifierFactory->createFromCallback($callback);
    }

    /**
     * Create a spy of a function in the global namespace, and declare it as a
     * function in another namespace.
     *
     * @param string $function  The name of the function in the global namespace.
     * @param string $namespace The namespace in which to create the new function.
     *
     * @return SpyVerifier The new spy.
     */
    public static function spyGlobal(
        string $function,
        string $namespace
    ): SpyVerifier {
        return static::driver()->spyVerifierFactory
            ->createGlobal($function, $namespace);
    }

    /**
     * Create a new stub.
     *
     * @param callable|null $callback The callback, or null to create an anonymous stub.
     *
     * @return StubVerifier The new stub.
     */
    public static function stub(callable $callback = null): StubVerifier
    {
        return static::driver()->stubVerifierFactory
            ->createFromCallback($callback);
    }

    /**
     * Create a stub of a function in the global namespace, and declare it as a
     * function in another namespace.
     *
     * Stubs created via this function do not forward to the original function
     * by default. This differs from stubs created by other methods.
     *
     * @param string $function  The name of the function in the global namespace.
     * @param string $namespace The namespace in which to create the new function.
     *
     * @return StubVerifier The new stub.
     */
    public static function stubGlobal(
        string $function,
        string $namespace
    ): StubVerifier {
        return static::driver()->stubVerifierFactory
            ->createGlobal($function, $namespace);
    }

    /**
     * Restores the behavior of any functions in the global namespace that have
     * been altered via spyGlobal() or stubGlobal().
     */
    public static function restoreGlobalFunctions()
    {
        return static::driver()->functionHookManager->restoreGlobalFunctions();
    }

    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection|null The result.
     */
    public static function checkInOrder(...$events)
    {
        return static::driver()->eventOrderVerifier->checkInOrder(...$events);
    }

    /**
     * Throws an exception unless the supplied events happened in chronological
     * order.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection The result.
     * @throws Throwable       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function inOrder(...$events): EventCollection
    {
        return static::driver()->eventOrderVerifier->inOrder(...$events);
    }

    /**
     * Checks that at least one event is supplied.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public static function checkAnyOrder(...$events)
    {
        return static::driver()->eventOrderVerifier->checkAnyOrder(...$events);
    }

    /**
     * Throws an exception unless at least one event is supplied.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Throwable                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function anyOrder(...$events): EventCollection
    {
        return static::driver()->eventOrderVerifier->anyOrder(...$events);
    }

    /**
     * Create a new matcher that matches anything.
     *
     * @return Matcher The newly created matcher.
     */
    public static function any(): Matcher
    {
        return static::driver()->matcherFactory->any();
    }

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value The value to check.
     *
     * @return Matcher The newly created matcher.
     */
    public static function equalTo($value): Matcher
    {
        return static::driver()->matcherFactory->equalTo($value, false);
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
    public static function wildcard(
        $value = null,
        int $minimumArguments = 0,
        int $maximumArguments = -1
    ): WildcardMatcher {
        return static::driver()->matcherFactory
            ->wildcard($value, $minimumArguments, $maximumArguments);
    }

    /**
     * Get an "empty" value for the supplied type.
     *
     * @param ReflectionType $type The type.
     *
     * @return mixed An "empty" value of the supplied type.
     */
    public static function emptyValue(ReflectionType $type)
    {
        return static::driver()->emptyValueFactory->fromType($type);
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
    public static function setExportDepth(int $depth): int
    {
        return static::driver()->exporter->setDepth($depth);
    }

    /**
     * Turn on or off the use of ANSI colored output.
     *
     * Pass `null` to detect automatically.
     *
     * @param bool|null $useColor True to use color.
     */
    public static function setUseColor(bool $useColor = null)
    {
        static::driver()->assertionRenderer->setUseColor($useColor);
        static::driver()->differenceEngine->setUseColor($useColor);
    }
}
