<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition;

use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionCollectionInterface;
use ReflectionClass;

/**
 * The interface implemented by mock definitions.
 */
interface MockDefinitionInterface
{
    /**
     * Get the types.
     *
     * @return array<string,ReflectionClass> The types.
     */
    public function types();

    /**
     * Get the custom methods.
     *
     * @return array<string,callable|null> The custom methods.
     */
    public function customMethods();

    /**
     * Get the custom properties.
     *
     * @return array<string,mixed> The custom properties.
     */
    public function customProperties();

    /**
     * Get the custom static methods.
     *
     * @return array<string,callable|null> The custom static methods.
     */
    public function customStaticMethods();

    /**
     * Get the custom static properties.
     *
     * @return array<string,mixed> The custom static properties.
     */
    public function customStaticProperties();

    /**
     * Get the custom constants.
     *
     * @return array<string,mixed> The custom constants.
     */
    public function customConstants();

    /**
     * Get the class name.
     *
     * @return string|null The class name.
     */
    public function className();

    /**
     * Get the type names.
     *
     * @return array<string> The type names.
     */
    public function typeNames();

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
     * Get the method definitions.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MethodDefinitionCollectionInterface The method definitions.
     */
    public function methods();

    /**
     * Check if the supplied definition is equal to this definition.
     *
     * @return boolean True if equal.
     */
    public function isEqualTo(MockDefinitionInterface $definition);
}
