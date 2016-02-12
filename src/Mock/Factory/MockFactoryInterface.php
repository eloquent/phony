<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Factory;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
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
     * @param MockBuilderInterface $builder   The builder.
     * @param boolean              $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass        The class.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createMockClass(
        MockBuilderInterface $builder,
        $createNew = false
    );

    /**
     * Create a new full mock instance for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createFullMock(MockBuilderInterface $builder);

    /**
     * Create a new partial mock instance for the supplied builder.
     *
     * @param MockBuilderInterface          $builder   The builder.
     * @param ArgumentsInterface|array|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createPartialMock(
        MockBuilderInterface $builder,
        $arguments = array()
    );
}
