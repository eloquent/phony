<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Builder\Definition\MockDefinitionInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Generator\MockGeneratorInterface;
use Eloquent\Phony\Mock\MockInterface;
use ReflectionClass;

/**
 * The interface implemented by mock builders.
 */
interface MockBuilderInterface
{
    /**
     * Get the types.
     *
     * @return array<string,ReflectionClass> The types.
     */
    public function types();

    /**
     * Add classes, interfaces, or traits.
     *
     * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface> $type      A type, or types to add.
     * @param string|ReflectionClass|MockBuilderInterface|array<string|ReflectionClass|MockBuilderInterface> $types,... Additional types to add.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If invalid input is supplied, or this builder is already finalized.
     */
    public function like($type);

    /**
     * Add custom methods and properties via a definition.
     *
     * @param array|object $definition The definition.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If invalid input is supplied, or this builder is already finalized.
     */
    public function define($definition);

    /**
     * Add a custom method.
     *
     * @param string        $name     The name.
     * @param callable|null $callback The callback.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If this builder is already finalized.
     */
    public function addMethod($name, $callback = null);

    /**
     * Add a custom property.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If this builder is already finalized.
     */
    public function addProperty($name, $value = null);

    /**
     * Add a custom static method.
     *
     * @param string        $name     The name.
     * @param callable|null $callback The callback.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If this builder is already finalized.
     */
    public function addStaticMethod($name, $callback = null);

    /**
     * Add a custom static property.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If this builder is already finalized.
     */
    public function addStaticProperty($name, $value = null);

    /**
     * Add a custom class constant.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If this builder is already finalized.
     */
    public function addConstant($name, $value);

    /**
     * Set the class name.
     *
     * @param string $className|null The class name, or null to use a generated name.
     *
     * @return MockBuilderInterface   This builder.
     * @throws MockExceptionInterface If this builder is already finalized.
     */
    public function named($className = null);

    /**
     * Returns true if this builder is finalized.
     *
     * @return boolean True if finalized.
     */
    public function isFinalized();

    /**
     * Finalize the mock builder.
     *
     * @return MockBuilderInterface This builder.
     */
    public function finalize();

    /**
     * Get the mock definitions.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MockDefinitionInterface The mock definition.
     */
    public function definition();

    /**
     * Returns true if the mock class has been built.
     *
     * @return boolean True if the mock class has been built.
     */
    public function isBuilt();

    /**
     * Generate and define the mock class.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param boolean|null $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass        The class.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function build($createNew = null);

    /**
     * Generate and define the mock class, and return the class name.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param boolean|null $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return string                 The class name.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function className($createNew = null);

    /**
     * Get a mock.
     *
     * This method will return the current mock, only creating a new mock if no
     * existing mock is available.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MockInterface          The mock instance.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function get();

    /**
     * Create a new mock.
     *
     * This method will always create a new mock, and will replace the current
     * mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param mixed $arguments,... The constructor arguments.
     *
     * @return MockInterface          The mock instance.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function create();

    /**
     * Create a new mock.
     *
     * This method will always create a new mock, and will replace the current
     * mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * This method supports reference parameters.
     *
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     * @param string|null                                  $label     The label.
     *
     * @return MockInterface          The mock instance.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createWith($arguments = null, $label = null);

    /**
     * Create a new full mock.
     *
     * This method will always create a new mock, and will replace the current
     * mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param string|null $label The label.
     *
     * @return MockInterface          The mock instance.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function full($label = null);

    /**
     * Get the generated source code of the mock class.
     *
     * Calling this method will finalize the mock builder.
     *
     * @internal
     *
     * @param MockGeneratorInterface|null $generator The mock generator to use.
     *
     * @return string                 The source code.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function source(MockGeneratorInterface $generator = null);
}
