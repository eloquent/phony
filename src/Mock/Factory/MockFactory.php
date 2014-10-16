<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Factory;

use Eloquent\Phony\Invocation\WrappedMethod;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;

/**
 * Creates mock instances.
 *
 * @internal
 */
class MockFactory implements MockFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MockFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Cosntruct a new mock factory.
     *
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
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
     * Create the mock class for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return ReflectionClass The class.
     */
    public function createMockClass(MockBuilderInterface $builder)
    {
        $className = $builder->className();
        $isNew = !class_exists($className);

        if ($isNew) {
            $builder->build();
        }

        $class = new ReflectionClass($className);

        if (!$isNew) {
            return $class;
        }

        $property = $class->getProperty('_staticStubs');
        $property->setAccessible(true);
        $property->setValue(
            null,
            array_map(
                function ($stub) {
                    return $stub->forwards();
                },
                $this->createStubs(
                    $builder->methodDefinitions()->staticMethods()
                )
            )
        );

        return $class;
    }

    /**
     * Create a new mock instance for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return MockInterface The newly created mock.
     */
    public function createMock(MockBuilderInterface $builder)
    {
        $class = $this->createMockClass($builder);
        $mock = $class->newInstanceArgs();

        $property = $class->getProperty('_stubs');
        $property->setAccessible(true);
        $property->setValue(
            $mock,
            $this->createStubs($builder->methodDefinitions()->methods(), $mock)
        );

        return $mock;
    }

    /**
     * Create the stubs for a list of methods.
     *
     * @param array<string,MethodDefinitionInterface> The methods.
     * @param MockInterface|null $mock The mock.
     *
     * @return array<string,StubVerifierInterface> The stubs.
     */
    protected function createStubs(array $methods, MockInterface $mock = null)
    {
        $stubs = array();

        foreach ($methods as $method) {
            if ($method->isCustom()) {
                $stubs[$method->name()] = $this->stubVerifierFactory
                    ->createFromCallback($method->callback(), $mock);
            } else {
                $stubs[$method->name()] = $this->stubVerifierFactory
                    ->createFromCallback(
                        new WrappedMethod($method->method(), $mock),
                        $mock
                    );
            }
        }

        return $stubs;
    }

    private static $instance;
    private $stubVerifierFactory;
}
