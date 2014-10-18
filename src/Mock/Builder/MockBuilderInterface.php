<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder;

use BadMethodCallException;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionCollectionInterface;
use Eloquent\Phony\Mock\Builder\Exception\MockBuilderExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;

/**
 * The interface implemented by mock builders.
 */
interface MockBuilderInterface
{
    /**
     * Add classes, interfaces, or traits.
     *
     * @param string|object|array<string|object> $type      A type, or types to add.
     * @param string|object|array<string|object> $types,... Additional types to add.
     *
     * @return MockBuilderInterface          This builder.
     * @throws MockBuilderExceptionInterface If invalid input is supplied.
     */
    public function like($type);

    /**
     * Add custom methods and properties via a definition.
     *
     * @param array|object $definition The definition.
     *
     * @return MockBuilderInterface This builder.
     */
    public function define($definition);

    /**
     * Add a custom method.
     *
     * @param string        $name     The name.
     * @param callable|null $callback The callback.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addMethod($name, $callback = null);

    /**
     * Add a custom static method.
     *
     * @param string        $name     The name.
     * @param callable|null $callback The callback.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addStaticMethod($name, $callback = null);

    /**
     * Add a custom property.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addProperty($name, $value = null);

    /**
     * Add a custom static property.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addStaticProperty($name, $value = null);

    /**
     * Add a custom class constant.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     */
    public function addConstant($name, $value);

    /**
     * Set the class name.
     *
     * @param string $className|null The class name, or null to use a generated name.
     *
     * @return MockBuilderInterface   This builder.
     * @throws FinalizedMockException If this builder is already finalized.
     */
    public function named($className = null);

    /**
     * Get the identifier.
     *
     * @return string|null The identifier.
     */
    public function id();

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className();

    /**
     * Get the parent class name.
     *
     * @return string|null The parent class name, or null if the mock will not extend a class.
     */
    public function parentClassName();

    /**
     * Get the interface names.
     *
     * @return array<string> The interface names.
     */
    public function interfaceNames();

    /**
     * Get the trait names.
     *
     * @return array<string> The trait names.
     */
    public function traitNames();

    /**
     * Get the types.
     *
     * @return array<string> The types.
     */
    public function types();

    /**
     * Get the type reflectors.
     *
     * @return array<string,ReflectionClass> The type reflectors.
     */
    public function reflectors();

    /**
     * Get the custom methods.
     *
     * @return array<string,callable|null> The custom methods.
     */
    public function methods();

    /**
     * Get the custom static methods.
     *
     * @return array<string,callable|null> The custom static methods.
     */
    public function staticMethods();

    /**
     * Get the custom properties.
     *
     * @return array<string,mixed> The custom properties.
     */
    public function properties();

    /**
     * Get the custom static properties.
     *
     * @return array<string,mixed> The custom static properties.
     */
    public function staticProperties();

    /**
     * Get the custom constants.
     *
     * @return array<string,mixed> The custom constants.
     */
    public function constants();

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
     * Get the method definitions.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MethodDefinitionCollectionInterface The method definitions.
     */
    public function methodDefinitions();

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
     * @return ReflectionClass The class.
     */
    public function build();

    /**
     * Get a mock.
     *
     * This method will return the current mock, only creating a new mock if no
     * existing mock is available.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MockInterface The mock instance.
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
     * @return MockInterface The mock instance.
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
     * @param array<integer,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return MockInterface The mock instance.
     */
    public function createWith(array $arguments = null);

    /**
     * Turn a mock into a full mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param MockInterface|null $mock The mock, or null to use the current mock.
     *
     * @return MockInterface The mock instance.
     */
    public function full(MockInterface $mock = null);

    /**
     * Get a static stub.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface         The stub verifier.
     * @throws MockBuilderExceptionInterface If the stub does not exist.
     */
    public function staticStub($name);

    /**
     * Get a stub.
     *
     * Calling this method will finalize the mock builder, unless a mock is
     * supplied.
     *
     * @param string             $name The method name.
     * @param MockInterface|null $mock The mock, or null to use the current mock.
     *
     * @return StubVerifierInterface         The stub verifier.
     * @throws MockBuilderExceptionInterface If the stub does not exist.
     */
    public function stub($name, MockInterface $mock = null);

    /**
     * Get a stub, and modify its current criteria to match the supplied
     * arguments (and possibly others).
     *
     * Calling this method will finalize the mock builder.
     *
     * @param string               $name      The method name.
     * @param array<integer,mixed> $arguments The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the stub does not exist.
     */
    public function __call($name, array $arguments);
}
