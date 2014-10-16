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
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubInterface;

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
        $mock = new $className();
        $mock->_setStubs(
            $this->createStubs($builder->methodDefinitions()->methods(), $mock)
        );

        return $mock;
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
        return array_map(
            function ($stub) {
                return $stub->forwards();
            },
            $this->createStubs($builder->staticMethods())
        );
    }

    /**
     * Create the stubs for a mock.
     *
     * @param MockBuilderInterface $builder The builder.
     * @param MockInterface|null   $mock    The mock.
     *
     * @return array<string,StubInterface> The stubs.
     */
    protected function createStubs(
        MockBuilderInterface $builder,
        MockInterface $mock = null
    ) {
        $stubs = array();

        foreach ($builder->methodDefinitions() as $name => $method) {
            if ($method->isCustom()) {
                $stubs[$name] = $this->stubVerifierFactory
                    ->createFromCallback($method->callback(), $mock);
            } else {
                $stubs[$name] = $this->stubVerifierFactory->createFromCallback(
                    new WrappedMethod($method->method(), $mock),
                    $mock
                );
            }
        }

        return $stubs;
    }

    private static $instance;
}
