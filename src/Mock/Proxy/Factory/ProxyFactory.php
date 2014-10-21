<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Factory;

use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\NonMockClassException;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Factory\MockFactoryInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\InstanceProxyInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\InstanceStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxy;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\StubbingProxy;
use Eloquent\Phony\Mock\Proxy\Verification\InstanceVerificationProxyInterface;
use Eloquent\Phony\Mock\Proxy\Verification\StaticVerificationProxy;
use Eloquent\Phony\Mock\Proxy\Verification\StaticVerificationProxyInterface;
use Eloquent\Phony\Mock\Proxy\Verification\VerificationProxy;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Creates proxies.
 *
 * @internal
 */
class ProxyFactory implements ProxyFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return ProxyFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new proxy factory.
     *
     * @param MockFactoryInterface|null         $mockFactory         The mock factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param WildcardMatcherInterface|null     $wildcardMatcher     The wildcard matcher to use.
     */
    public function __construct(
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

        $this->mockFactory = $mockFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->wildcardMatcher = $wildcardMatcher;
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
     * Create a new static stubbing proxy.
     *
     * @param ProxyInterface|ReflectionClass|object|string $class The class.
     *
     * @return StaticStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface       If the supplied class name is not a mock class.
     */
    public function createStubbingStatic($class)
    {
        list($class, $stubs, $magicStubsProperty) =
            $this->prepareStatic($class);

        return new StaticStubbingProxy(
            $class,
            $stubs,
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
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
    public function createStubbing($mock)
    {
        list($mock, $class, $stubs, $magicStubsProperty) =
            $this->prepareInstance($mock);

        return new StubbingProxy(
            $mock,
            $class,
            $stubs,
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
    }

    /**
     * Create a new static verification proxy.
     *
     * @param ProxyInterface|ReflectionClass|object|string $class The class.
     *
     * @return StaticVerificationProxyInterface The newly created proxy.
     * @throws MockExceptionInterface           If the supplied class name is not a mock class.
     */
    public function createVerificationStatic($class)
    {
        list($class, $stubs, $magicStubsProperty) =
            $this->prepareStatic($class);

        return new StaticVerificationProxy(
            $class,
            $stubs,
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
    }

    /**
     * Create a new verification proxy.
     *
     * @param MockInterface|InstanceProxyInterface $mock The mock.
     *
     * @return InstanceVerificationProxyInterface The newly created proxy.
     * @throws MockExceptionInterface             If the supplied mock is invalid.
     */
    public function createVerification($mock)
    {
        list($mock, $class, $stubs, $magicStubsProperty) =
            $this->prepareInstance($mock);

        return new VerificationProxy(
            $mock,
            $class,
            $stubs,
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
    }

    /**
     * Prepare the arguments for a static proxy.
     *
     * @param ProxyInterface|ReflectionClass|object|string $class The class.
     *
     * @return array<integer,mixed>   The arguments.
     * @throws MockExceptionInterface If the supplied class name is not a mock class.
     */
    protected function prepareStatic($class)
    {
        if ($class instanceof ProxyInterface) {
            $class = new ReflectionClass($class->className());
        } elseif (!$class instanceof ReflectionClass) {
            try {
                $class = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new NonMockClassException($class, $e);
            }
        }

        $className = $class->getName();

        if (!$class->isSubclassOf('Eloquent\Phony\Mock\MockInterface')) {
            throw new NonMockClassException($className);
        }

        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $stubs = $stubsProperty->getValue(null);

        if ($class->hasMethod('__callStatic')) {
            $magicStubsProperty = $class->getProperty('_magicStaticStubs');
            $magicStubsProperty->setAccessible(true);
        } else {
            $magicStubsProperty = null;
        }

        return array($class, $stubs, $magicStubsProperty);
    }

    /**
     * Prepare the arguments for an instance proxy.
     *
     * @param MockInterface|InstanceProxyInterface $mock The mock.
     *
     * @return array<integer,mixed>   The arguments.
     * @throws MockExceptionInterface If the supplied mock is invalid.
     */
    protected function prepareInstance($mock)
    {
        if ($mock instanceof InstanceProxyInterface) {
            $mock = $mock->mock();
        } elseif (!$mock instanceof MockInterface) {
            throw new NonMockClassException(get_class($mock));
        }

        $class = new ReflectionClass($mock);

        $stubsProperty = $class->getProperty('_stubs');
        $stubsProperty->setAccessible(true);
        $stubs = $stubsProperty->getValue($mock);

        if ($class->hasMethod('__call')) {
            $magicStubsProperty = $class->getProperty('_magicStubs');
            $magicStubsProperty->setAccessible(true);
        } else {
            $magicStubsProperty = null;
        }

        return array($mock, $class, $stubs, $magicStubsProperty);
    }

    private static $instance;
    private $mockFactory;
    private $stubVerifierFactory;
    private $wildcardMatcher;
}
