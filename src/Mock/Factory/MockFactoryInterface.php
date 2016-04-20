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
use Eloquent\Phony\Mock\Builder\Definition\MockDefinitionInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
use ReflectionClass;

/**
 * The interface implemented by mock factories.
 */
interface MockFactoryInterface
{
    /**
     * Create the mock class for the supplied definition.
     *
     * @param MockDefinitionInterface $definition The definition.
     * @param boolean                 $createNew  True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass        The class.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createMockClass(
        MockDefinitionInterface $definition,
        $createNew = false
    );

    /**
     * Create a new full mock instance for the supplied class.
     *
     * @param ReflectionClass $class The class.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createFullMock(ReflectionClass $class);

    /**
     * Create a new partial mock instance for the supplied definition.
     *
     * @param ReflectionClass               $class     The class.
     * @param ArgumentsInterface|array|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createPartialMock(
        ReflectionClass $class,
        $arguments = array()
    );
}
