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

/**
 * The interface implemented by method definition collections.
 */
interface MethodDefinitionCollectionInterface
{
    /**
     * Get the methods.
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
     * Get the public non-static methods.
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
     * Get the protected non-static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function protectedMethods();
}
