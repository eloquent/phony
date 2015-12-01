<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Factory;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Factory\MockFactoryInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactoryInterface;

/**
 * Creates mock builders.
 */
class MockBuilderFactory implements MockBuilderFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MockBuilderFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new mock builder factory.
     *
     * @param MockFactoryInterface|null  $mockFactory  The mock factory to use.
     * @param ProxyFactoryInterface|null $proxyFactory The proxy factory to use.
     */
    public function __construct(
        MockFactoryInterface $mockFactory = null,
        ProxyFactoryInterface $proxyFactory = null
    ) {
        if (null === $mockFactory) {
            $mockFactory = MockFactory::instance();
        }
        if (null === $proxyFactory) {
            $proxyFactory = ProxyFactory::instance();
        }

        $this->mockFactory = $mockFactory;
        $this->proxyFactory = $proxyFactory;
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
     * Get the proxy factory.
     *
     * @return ProxyFactoryInterface The proxy factory.
     */
    public function proxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * Create a new mock builder.
     *
     * The `$types` argument may be a class name, a reflection class, or a mock
     * builder. It may also be an array of any of these.
     *
     * If `$types` is omitted, or `null`, no existing type will be used when
     * generating the mock class. This is useful in the case of ad hoc mocks,
     * where mocks need not imitate an existing type.
     *
     * @param mixed             $types      The types to mock.
     * @param array|object|null $definition The definition.
     * @param string|null       $className  The class name.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public function create(
        $types = null,
        $definition = null,
        $className = null
    ) {
        return new MockBuilder(
            $types,
            $definition,
            $className,
            $this->mockFactory,
            $this->proxyFactory
        );
    }

    /**
     * Create a new full mock.
     *
     * The `$types` argument may be a class name, a reflection class, or a mock
     * builder. It may also be an array of any of these.
     *
     * If `$types` is omitted, or `null`, no existing type will be used when
     * generating the mock class. This is useful in the case of ad hoc mocks,
     * where mocks need not imitate an existing type.
     *
     * @param mixed             $types      The types to mock.
     * @param array|object|null $definition The definition.
     * @param string|null       $className  The class name.
     *
     * @return MockInterface The mock.
     */
    public function createFullMock(
        $types = null,
        $definition = null,
        $className = null
    ) {
        return $this->create($types, $definition, $className)->full();
    }

    /**
     * Create a new partial mock.
     *
     * The `$types` argument may be a class name, a reflection class, or a mock
     * builder. It may also be an array of any of these.
     *
     * If `$types` is omitted, or `null`, no existing type will be used when
     * generating the mock class. This is useful in the case of ad hoc mocks,
     * where mocks need not imitate an existing type.
     *
     * @param mixed                         $types      The types to mock.
     * @param ArgumentsInterface|array|null $arguments  The constructor arguments, or null to bypass the constructor.
     * @param array|object|null             $definition The definition.
     * @param string|null                   $className  The class name.
     *
     * @return MockInterface The mock.
     */
    public function createPartialMock(
        $types = null,
        $arguments = null,
        $definition = null,
        $className = null
    ) {
        if (null !== $arguments || func_num_args() < 2) {
            $arguments = Arguments::adapt($arguments);
        }

        return $this->create($types, $definition, $className)
            ->createWith($arguments);
    }

    private static $instance;
    private $mockFactory;
    private $proxyFactory;
}
