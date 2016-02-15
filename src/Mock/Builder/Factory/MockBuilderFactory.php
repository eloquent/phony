<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
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
use Eloquent\Phony\Mock\Handle\Factory\HandleFactory;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactoryInterface;
use Eloquent\Phony\Mock\MockInterface;

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
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new mock builder factory.
     *
     * @param MockFactoryInterface|null   $mockFactory   The mock factory to use.
     * @param HandleFactoryInterface|null $handleFactory The handle factory to use.
     */
    public function __construct(
        MockFactoryInterface $mockFactory = null,
        HandleFactoryInterface $handleFactory = null
    ) {
        if (!$mockFactory) {
            $mockFactory = MockFactory::instance();
        }
        if (!$handleFactory) {
            $handleFactory = HandleFactory::instance();
        }

        $this->mockFactory = $mockFactory;
        $this->handleFactory = $handleFactory;
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
     * Get the handle factory.
     *
     * @return HandleFactoryInterface The handle factory.
     */
    public function handleFactory()
    {
        return $this->handleFactory;
    }

    /**
     * Create a new mock builder.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * @param mixed $types The types to mock.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public function create($types = array())
    {
        return new MockBuilder($types, $this->mockFactory, $this->handleFactory);
    }

    /**
     * Create a new full mock.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * @param mixed $types The types to mock.
     *
     * @return MockInterface The mock.
     */
    public function createFullMock($types = array())
    {
        $builder =
            new MockBuilder($types, $this->mockFactory, $this->handleFactory);

        return $builder->full();
    }

    /**
     * Create a new partial mock.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * Omitting `$arguments` will cause the original constructor to be called
     * with an empty argument list. However, if a `null` value is supplied for
     * `$arguments`, the original constructor will not be called at all.
     *
     * @param mixed                         $types     The types to mock.
     * @param ArgumentsInterface|array|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return MockInterface The mock.
     */
    public function createPartialMock($types = array(), $arguments = array())
    {
        $builder =
            new MockBuilder($types, $this->mockFactory, $this->handleFactory);

        return $builder->partialWith($arguments);
    }

    private static $instance;
    private $mockFactory;
    private $handleFactory;
}
