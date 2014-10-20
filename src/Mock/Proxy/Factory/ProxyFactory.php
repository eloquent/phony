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
     * @param ProxyInterface|ReflectionClass|object|string $class The class.
     *
     * @return StaticStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface       If the supplied class name is not a mock class.
     */
    public function createStubbingStatic($class)
    {
        list($className, $stubs) = $this->prepareStatic($class);

        return new StaticStubbingProxy($className, $stubs);
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
        list($mock, $stubs) = $this->prepareInstance($mock);

        return new StubbingProxy($mock, $stubs);
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
        list($className, $stubs) = $this->prepareStatic($class);

        return new StaticVerificationProxy($className, $stubs);
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
        list($mock, $stubs) = $this->prepareInstance($mock);

        return new VerificationProxy($mock, $stubs);
    }

    /**
     * Prepare the arguments for a static proxy.
     *
     * @param ProxyInterface|ReflectionClass|object|string $class The class.
     *
     * @return tuple<string,array<string,StubVerifierInterface>> The arguments.
     * @throws MockExceptionInterface                            If the supplied class name is not a mock class.
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

        return array($class->getName(), $this->wrapStubs($stubs));
    }

    /**
     * Prepare the arguments for an instance proxy.
     *
     * @param MockInterface|InstanceProxyInterface $mock The mock.
     *
     * @return tuple<MockInterface,array<string,StubVerifierInterface>> The arguments.
     * @throws MockExceptionInterface                                   If the supplied mock is invalid.
     */
    protected function prepareInstance($mock)
    {
        if ($mock instanceof InstanceProxyInterface) {
            $mock = $mock->mock();
        } elseif (!$mock instanceof MockInterface) {
            throw new NonMockClassException(get_class($mock));
        }

        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $stubs = $stubsProperty->getValue($mock);

        return array($mock, $this->wrapStubs($stubs));
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
