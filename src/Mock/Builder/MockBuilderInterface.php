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

use Eloquent\Phony\Mock\Builder\Exception\MockBuilderExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
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
     * Finalize the mock builder, generate the mock class, and return a new
     * instance.
     *
     * @param boolean|null $createNew True if a new instance should be created.
     *
     * @return MockInterface The newly created mock instance.
     */
    public function get($createNew = null);

    /**
     * Finalize the mock builder, generate the mock class, and return the class
     * name.
     *
     * @return string The class name.
     */
    public function build();

    /**
     * Finalize the mock builder, generate the mock class, and return the source
     * code.
     *
     * @return string The source code.
     */
    public function source();

    /**
     * Finalize the mock builder.
     *
     * @return MockBuilderInterface This builder.
     */
    public function finalize();

    /**
     * Get the identifier.
     *
     * @return integer|null The identifier.
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
}
