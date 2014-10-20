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

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\NonMockClassException;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\InstanceStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxy;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\StubbingProxy;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

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
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory THe stub verifier factory to use.
     */
    public function __construct(
        StubVerifierFactoryInterface $stubVerifierFactory = null
    ) {
        if (null === $stubVerifierFactory) {
            $stubVerifierFactory = StubVerifierFactory::instance();
        }

        $this->stubVerifierFactory = $stubVerifierFactory;
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
     * Create a new static stubbing proxy.
     *
     * @param ReflectionClass|object|string $class The class.
     *
     * @return StaticStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface       If the supplied class name is not a mock class.
     */
    public function createStubbingStatic($class)
    {
        if (!$class instanceof ReflectionClass) {
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

        return new StaticStubbingProxy(
            $class->getName(),
            $this->wrapStubs($stubs)
        );
    }

    /**
     * Create a new stubbing proxy.
     *
     * @param MockInterface $mock The mock.
     *
     * @return InstanceStubbingProxyInterface The newly created proxy.
     */
    public function createStubbing(MockInterface $mock)
    {
        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $stubs = $stubsProperty->getValue($mock);

        return new StubbingProxy($mock, $this->wrapStubs($stubs));
    }

    /**
     * Wrap the supplied stub spies in stub verifiers.
     *
     * @param array<string,SpyInterface> $stubs The stubs.
     *
     * @return array<string,StubVerifierInterface> The wrapped stubs.
     */
    protected function wrapStubs(array $stubs)
    {
        foreach ($stubs as $name => $stub) {
            $stubs[$name] =
                $this->stubVerifierFactory->create($stub->callback(), $stub);
        }

        return $stubs;
    }

    private static $instance;
    private $stubVerifierFactory;
}
