<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Factory;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\MockInterface;

/**
 * The interface implemented by mock builder factories.
 */
interface MockBuilderFactoryInterface
{
    /**
     * Create a new mock builder.
     *
     * @param array<string|object>|string|object|null $types      The types to mock.
     * @param array|object|null                       $definition The definition.
     * @param string|null                             $className  The class name.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public function create(
        $types = null,
        $definition = null,
        $className = null
    );

    /**
     * Create a new mock.
     *
     * @param array<string|object>|string|object|null      $types      The types to mock.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments  The constructor arguments, or null to bypass the constructor.
     * @param array|object|null                            $definition The definition.
     * @param string|null                                  $className  The class name.
     *
     * @return MockInterface The mock.
     */
    public function createMock(
        $types = null,
        $arguments = null,
        $definition = null,
        $className = null
    );

    /**
     * Create a new full mock.
     *
     * @param array<string|object>|string|object|null $types      The types to mock.
     * @param array|object|null                       $definition The definition.
     * @param string|null                             $className  The class name.
     *
     * @return MockInterface The mock.
     */
    public function createFullMock(
        $types = null,
        $definition = null,
        $className = null
    );
}
