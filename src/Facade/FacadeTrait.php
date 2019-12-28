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
        $container = self::$globals::$container;

        return $container->mockBuilderFactory->create($types);
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
        $container = self::$globals::$container;

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
    public static function partialMock(
        $types = [],
        $arguments = []
    ): InstanceHandle {
        $container = self::$globals::$container;

        return $container->handleFactory->instanceHandle(
            $container->mockBuilderFactory->create($types)
                ->partialWith($arguments)
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
        $container = self::$globals::$container;

        return $container->handleFactory->instanceHandle($mock);
    }

    /**
     * Create a new static handle.
     *
     * @param Mock|Handle|ReflectionClass<object>|string $class The class.
     *
     * @return StaticHandle  The newly created handle.
     * @throws MockException If the supplied class name is not a mock class.
     */
    public static function onStatic($class): StaticHandle
    {
        $container = self::$globals::$container;

        return $container->handleFactory->staticHandle($class);
    }

    /**
     * Create a new spy.
     *
     * @param ?callable $callback The callback, or null to create an anonymous spy.
     *
     * @return SpyVerifier The new spy.
     */
    public static function spy(callable $callback = null): SpyVerifier
    {
        $container = self::$globals::$container;

        return $container->spyVerifierFactory->createFromCallback($callback);
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
    public static function spyGlobal(
        string $function,
        string $namespace
    ): SpyVerifier {
        $container = self::$globals::$container;

        return $container->spyVerifierFactory
            ->createGlobal($function, $namespace);
    }

    /**
     * Create a new stub.
     *
     * @param ?callable $callback The callback, or null to create an anonymous stub.
     *
     * @return StubVerifier The new stub.
     */
    public static function stub(callable $callback = null): StubVerifier
    {
        $container = self::$globals::$container;

        return $container->stubVerifierFactory->createFromCallback($callback);
    }

    /**
     * Create a stub of a function in the global namespace, and declare it as a
     * function in another namespace.
     *
     * Stubs created via this function do not forward to the original function
     * by default. This differs from stubs created by other methods.
     *
     * @param callable&string $function  The name of the function in the global namespace.
     * @param string          $namespace The namespace in which to create the new function.
     *
     * @return StubVerifier The new stub.
     */
    public static function stubGlobal(
        string $function,
        string $namespace
    ): StubVerifier {
        $container = self::$globals::$container;

        return $container->stubVerifierFactory
            ->createGlobal($function, $namespace);
    }

    /**
     * Restores the behavior of any functions in the global namespace that have
     * been altered via spyGlobal() or stubGlobal().
     */
    public static function restoreGlobalFunctions(): void
    {
        $container = self::$globals::$container;

        $container->functionHookManager->restoreGlobalFunctions();
    }

    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return ?EventCollection The result.
     */
    public static function checkInOrder(object ...$events): ?EventCollection
    {
        $container = self::$globals::$container;

        return $container->eventOrderVerifier->checkInOrder(...$events);
    }

    /**
     * Throws an exception unless the supplied events happened in chronological
     * order.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function inOrder(object ...$events): ?EventCollection
    {
        $container = self::$globals::$container;

        return $container->eventOrderVerifier->inOrder(...$events);
    }

    /**
     * Checks that at least one event is supplied.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return ?EventCollection         The result.
     * @throws InvalidArgumentException If invalid input is supplied.
     */
    public static function checkAnyOrder(object ...$events): ?EventCollection
    {
        $container = self::$globals::$container;

        return $container->eventOrderVerifier->checkAnyOrder(...$events);
    }

    /**
     * Throws an exception unless at least one event is supplied.
     *
     * @param Event|EventCollection ...$events The events.
     *
     * @return ?EventCollection         The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidArgumentException If invalid input is supplied.
     * @throws Throwable                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public static function anyOrder(object ...$events): ?EventCollection
    {
        $container = self::$globals::$container;

        return $container->eventOrderVerifier->anyOrder(...$events);
    }

    /**
     * Create a new matcher that matches anything.
     *
     * @return Matcher The newly created matcher.
     */
    public static function any(): Matcher
    {
        $container = self::$globals::$container;

        return $container->matcherFactory->any();
    }

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value The value to check against.
     *
     * @return Matcher The newly created matcher.
     */
    public static function equalTo($value): Matcher
    {
        $container = self::$globals::$container;

        return $container->matcherFactory->equalTo($value, false);
    }

    /**
     * Create a new instance of matcher.
     *
     * @param string|object $type The type to check against.
     *
     * @return Matcher The newly created matcher.
     */
    public static function anInstanceOf($type): Matcher
    {
        $container = self::$globals::$container;

        return $container->matcherFactory->anInstanceOf($type);
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
        $container = self::$globals::$container;

        return $container->matcherFactory
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
        $container = self::$globals::$container;

        return $container->emptyValueFactory->fromType($type);
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
        $container = self::$globals::$container;

        return $container->exporter->setDepth($depth);
    }

    /**
     * Turn on or off the use of ANSI colored output.
     *
     * Pass `null` to detect automatically.
     *
     * @param ?bool $useColor True to use color.
     */
    public static function setUseColor(?bool $useColor): void
    {
        $container = self::$globals::$container;

        $container->assertionRenderer->setUseColor($useColor);
        $container->differenceEngine->setUseColor($useColor);
    }
}
