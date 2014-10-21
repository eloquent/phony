<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Factory\MockFactoryInterface;
use Eloquent\Phony\Mock\Proxy\Exception\UndefinedPropertyException;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use Eloquent\Phony\Stub\StubInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * An abstract base class for implementing proxies.
 *
 * @internal
 */
abstract class AbstractProxy implements ProxyInterface
{
    /**
     * Construct a new proxy.
     *
     * @param ReflectionClass                   $class               The class.
     * @param array<string,StubInterface>       $stubs               The stubs.
     * @param ReflectionProperty|null           $magicStubsProperty  The magic stubs property, or null if magic is not available.
     * @param MockFactoryInterface|null         $mockFactory         The mock factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param WildcardMatcherInterface|null     $wildcardMatcher     The wildcard matcher to use.
     */
    public function __construct(
        ReflectionClass $class,
        array $stubs,
        ReflectionProperty $magicStubsProperty = null,
        MockFactoryInterface $mockFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        WildcardMatcherInterface $wildcardMatcher = null
    ) {
        if (null === $mockFactory) {
            $mockFactory = MockFactory::instance();
        }
        if (null === $stubVerifierFactory) {
            $stubVerifierFactory = StubVerifierFactory::instance();
        }
        if (null === $wildcardMatcher) {
            $wildcardMatcher = WildcardMatcher::instance();
        }

        $this->class = $class;
        $this->stubs = $stubs;
        $this->magicStubsProperty = $magicStubsProperty;
        $this->mockFactory = $mockFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->wildcardMatcher = $wildcardMatcher;
    }

    /**
     * Get the class.
     *
     * @return ReflectionClass The class.
     */
    public function reflector()
    {
        return $this->class;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className()
    {
        return $this->class->getName();
    }

    /**
     * Get the stubs.
     *
     * @return array<string,SpyInterface> The stubs.
     */
    public function stubs()
    {
        return $this->stubs;
    }

    /**
     * Get the magic stubs.
     *
     * @return array<string,SpyInterface> The magic stubs.
     */
    public function magicStubs()
    {
        return $this->magicStubsProperty->getValue($this->mock);
    }

    /**
     * Get the magic stubs property.
     *
     * @return ReflectionProperty|null The magic stubs property, or null if magic calls are not supported.
     */
    public function magicStubsProperty()
    {
        return $this->magicStubsProperty;
    }

    /**
     * Get the mock factory.
     *
     * @return MockFactoryInterface The mock factory.
     */
    public function mockFactory()
    {
        return $this->mockFactory;
    }

    /**
     * Get the stub verifier factory.
     *
     * @return StubVerifierFactoryInterface The stub verifier factory.
     */
    public function stubVerifierFactory()
    {
        return $this->stubVerifierFactory;
    }

    /**
     * Get the wildcard matcher.
     *
     * @return WildcardMatcherInterface The wildcard matcher.
     */
    public function wildcardMatcher()
    {
        return $this->wildcardMatcher;
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function stub($name)
    {
        if (isset($this->stubs[$name])) {
            $stub = $this->stubs[$name];

            return $this->stubVerifierFactory->create($stub->callback(), $stub);
        }

        return $this->magicStub($name);
    }

    /**
     * Get a magic stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If magic calls are not supported.
     */
    public function magicStub($name)
    {
        if (null === $this->magicStubsProperty) {
            throw new UndefinedMethodStubException(
                $this->class->getName(),
                $name
            );
        }

        $magicStubs = $this->magicStubsProperty->getValue($this->mock);

        if (!isset($magicStubs[$name])) {
            $magicStubs[$name] = $this->wrapStub(
                $this->mockFactory
                    ->createMagicStub($this->class, $name, $this->mock)
            );
        }

        $this->magicStubsProperty->setValue($this->mock, $magicStubs);

        return $this->wrapStub($magicStubs[$name]);
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __get($name)
    {
        try {
            $stub = $this->stub($name);
        } catch (UndefinedMethodStubException $e) {
            throw new UndefinedPropertyException(get_called_class(), $name, $e);
        }

        return $stub;
    }

    /**
     * Wrap a stub in a stub verifier.
     *
     * @param SpyInterface $stub The stub.
     *
     * @return StubVerifierInterface The stub verifier.
     */
    protected function wrapStub(SpyInterface $stub)
    {
        return $this->stubVerifierFactory->create($stub->callback(), $stub);
    }

    protected $mock;
    protected $class;
    protected $stubs;
    protected $magicStubsProperty;
    protected $mockFactory;
    protected $stubVerifierFactory;
    protected $wildcardMatcher;
}
