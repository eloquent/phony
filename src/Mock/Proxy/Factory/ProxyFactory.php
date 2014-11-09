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
use Eloquent\Phony\Mock\Exception\InvalidMockClassException;
use Eloquent\Phony\Mock\Exception\InvalidMockException;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\NonMockClassException;
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
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
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
     * @param StubFactoryInterface|null         $stubFactory         The stub factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param WildcardMatcherInterface|null     $wildcardMatcher     The wildcard matcher to use.
     */
    public function __construct(
        StubFactoryInterface $stubFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        WildcardMatcherInterface $wildcardMatcher = null
    ) {
        if (null === $stubFactory) {
            $stubFactory = StubFactory::instance();
        }
        if (null === $stubVerifierFactory) {
            $stubVerifierFactory = StubVerifierFactory::instance();
        }
        if (null === $wildcardMatcher) {
            $wildcardMatcher = WildcardMatcher::instance();
        }

        $this->stubFactory = $stubFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->wildcardMatcher = $wildcardMatcher;
    }

    /**
     * Get the stub factory.
     *
     * @return StubFactoryInterface The stub factory.
     */
    public function stubFactory()
    {
        return $this->stubFactory;
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
     * Create a new stubbing proxy.
     *
     * @param MockInterface|InstanceProxyInterface $mock  The mock.
     * @param string|null                          $label The label.
     *
     * @return InstanceStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface         If the supplied mock is invalid.
     */
    public function createStubbing($mock, $label = null)
    {
        if ($mock instanceof InstanceStubbingProxyInterface) {
            return $mock;
        }

        if ($mock instanceof InstanceProxyInterface) {
            $mock = $mock->mock();
        }

        if ($mock instanceof MockInterface) {
            $class = new ReflectionClass($mock);

            $proxyProperty = $class->getProperty('_proxy');
            $proxyProperty->setAccessible(true);

            if ($proxy = $proxyProperty->getValue($mock)) {
                return $proxy;
            }
        } else {
            throw new InvalidMockException($mock);
        }

        return new StubbingProxy(
            $mock,
            (object) array(
                'stubs' => (object) array(),
                'isFull' => false,
                'label' => $label,
            ),
            $this->stubFactory,
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
        if ($mock instanceof InstanceVerificationProxyInterface) {
            return $mock;
        }

        $stubbingProxy = $this->createStubbing($mock);

        return new VerificationProxy(
            $stubbingProxy->mock(),
            $stubbingProxy->state(),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
    }

    /**
     * Create a new static stubbing proxy.
     *
     * @param MockInterface|ProxyInterface|ReflectionClass|string $class The class.
     *
     * @return StaticStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface       If the supplied class name is not a mock class.
     */
    public function createStubbingStatic($class)
    {
        if ($class instanceof StaticStubbingProxyInterface) {
            return $class;
        }

        if ($class instanceof ProxyInterface) {
            $class = $class->clazz();
        } elseif ($class instanceof MockInterface) {
            $class = new ReflectionClass($class);
        } elseif (is_string($class)) {
            try {
                $class = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new NonMockClassException($class, $e);
            }
        } elseif (!$class instanceof ReflectionClass) {
            throw new InvalidMockClassException($class);
        }

        if (!$class->isSubclassOf('Eloquent\Phony\Mock\MockInterface')) {
            throw new NonMockClassException($class->getName());
        }

        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);

        if ($proxy = $proxyProperty->getValue(null)) {
            return $proxy;
        }

        return new StaticStubbingProxy(
            $class,
            null,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
    }

    /**
     * Create a new static verification proxy.
     *
     * @param MockInterface|ProxyInterface|ReflectionClass|string $class The class.
     *
     * @return StaticVerificationProxyInterface The newly created proxy.
     * @throws MockExceptionInterface           If the supplied class name is not a mock class.
     */
    public function createVerificationStatic($class)
    {
        if ($class instanceof StaticVerificationProxyInterface) {
            return $class;
        }

        $stubbingProxy = $this->createStubbingStatic($class);

        return new StaticVerificationProxy(
            $stubbingProxy->clazz(),
            $stubbingProxy->state(),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
    }

    private static $instance;
    private $mockFactory;
    private $stubVerifierFactory;
    private $wildcardMatcher;
}
