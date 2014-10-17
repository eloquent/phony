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
use ReflectionClass;

/**
 * The interface implemented by mock factories.
 */
interface MockFactoryInterface
{
    /**
     * Create the mock class for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return ReflectionClass The class.
     */
    public function createMockClass(MockBuilderInterface $builder);

    /**
     * Create a new mock instance for the supplied builder.
     *
     * @param MockBuilderInterface      $builder   The builder.
     * @param array<integer,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return MockInterface The newly created mock.
     */
    public function createMock(
        MockBuilderInterface $builder,
        array $arguments = null
    );
}
