<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition\Method;

use ReflectionMethod;

/**
 * The interface implemented by method definition collections.
 */
interface MethodDefinitionCollectionInterface
{
    /**
     * Get the canonical method name for the supplied method name.
     *
     * @param string $name The method name.
     *
     * @return string|null The canonical method name, or null if no such method exists.
     */
    public function methodName($name);

    /**
     * Get the methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function allMethods();

    /**
     * Get the static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function staticMethods();

    /**
     * Get the instance methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function methods();

    /**
     * Get the public static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function publicStaticMethods();

    /**
     * Get the public instance methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function publicMethods();

    /**
     * Get the protected static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function protectedStaticMethods();

    /**
     * Get the protected instance methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function protectedMethods();

    /**
     * Get the trait methods.
     *
     * @return array<integer,ReflectionMethod> The trait methods.
     */
    public function traitMethods();
}
