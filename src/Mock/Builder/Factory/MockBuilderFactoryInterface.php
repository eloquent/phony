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
    );

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
    );

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
    );
}
