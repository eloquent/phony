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

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Spy\SpyInterface;
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
     * @param boolean|null         $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass        The class.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createMockClass(
        MockBuilderInterface $builder,
        $createNew = null
    );

    /**
     * Create a new mock instance for the supplied builder.
     *
     * @param MockBuilderInterface                         $builder   The builder.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     * @param string|null                                  $id        The identifier.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createMock(
        MockBuilderInterface $builder,
        $arguments = null,
        $id = null
    );

    /**
     * Create the stubs for a list of methods.
     *
     * @param ReflectionClass    $class The mock class.
     * @param array<string,MethodDefinitionInterface> The methods.
     * @param MockInterface|null $mock  The mock, or null for static stubs.
     *
     * @return array<string,SpyInterface> The stubs.
     */
    public function createStubs(
        ReflectionClass $class,
        array $methods,
        MockInterface $mock = null
    );

    /**
     * Create a magic stub.
     *
     * @param ReflectionClass    $class The mock class.
     * @param string             $name  The method name.
     * @param MockInterface|null $mock  The mock, or null for a static stub.
     *
     * @return SpyInterface The stub.
     */
    public function createMagicStub(
        ReflectionClass $class,
        $name,
        MockInterface $mock = null
    );
}
