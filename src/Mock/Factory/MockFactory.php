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

use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubInterface;
use ReflectionMethod;

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
     * Create a new mock instance for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return MockInterface The newly created mock.
     */
    public function createMock(MockBuilderInterface $builder)
    {
        $className = $builder->build();

        return new $className($this->createStubsForMock($builder));
    }

    /**
     * Create static stubs for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return array<string,StubInterface> The stubs.
     */
    public function createStaticStubs(MockBuilderInterface $builder)
    {
        return $this
            ->createForwardStubsForMethods($builder->staticMethodReflectors());
    }

    /**
     * Create the stubs for a regular mock.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return array<string,StubInterface> The stubs.
     */
    public function createStubsForMock(MockBuilderInterface $builder)
    {
        $stubs = array();

        foreach ($builder->methodReflectors() as $name => $method) {
            $stubs[$name] = $this->stubVerifierFactory
                ->createFromFunction($method);
        }

        return $stubs;
    }

    /**
     * Create stubs that forward for each of the supplied method reflectors.
     *
     * @param array<string,ReflectionMethod> $methods The methods.
     *
     * @return array<string,StubInterface> The stubs.
     */
    protected function createForwardStubsForMethods(array $methods)
    {
        $stubs = array();

        foreach ($methods as $name => $method) {
            $stubs[$name] = $this->stubVerifierFactory
                ->createFromFunction($method)
                ->forwards();
        }

        return $stubs;
    }

    private static $instance;
}
