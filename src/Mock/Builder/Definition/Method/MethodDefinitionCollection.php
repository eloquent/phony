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
 * Represents a collection of methods.
 *
 * @internal
 */
class MethodDefinitionCollection implements MethodDefinitionCollectionInterface
{
    /**
     * Construct a new custom method definition.
     *
     * @param array<string,MethodDefinitionInterface>|null       $methods      The methods.
     * @param array<integer,TraitMethodDefinitionInterface>|null $traitMethods The trait methods.
     */
    public function __construct(
        array $methods = null,
        array $traitMethods = null
    ) {
        if (null === $methods) {
            $methods = array();
        }
        if (null === $traitMethods) {
            $traitMethods = array();
        }

        $this->allMethods = $methods;
        $this->traitMethods = $traitMethods;
        $this->staticMethods = array();
        $this->methods = array();
        $this->publicStaticMethods = array();
        $this->publicMethods = array();
        $this->protectedStaticMethods = array();
        $this->protectedMethods = array();

        foreach ($methods as $name => $method) {
            $isStatic = $method->isStatic();
            $accessLevel = $method->accessLevel();
            $isPublic = 'public' === $accessLevel;

            if ($isStatic) {
                $this->staticMethods[$name] = $method;

                if ($isPublic) {
                    $this->publicStaticMethods[$name] = $method;
                } else {
                    $this->protectedStaticMethods[$name] = $method;
                }
            } else {
                $this->methods[$name] = $method;

                if ($isPublic) {
                    $this->publicMethods[$name] = $method;
                } else {
                    $this->protectedMethods[$name] = $method;
                }
            }
        }
    }

    /**
     * Get the methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function allMethods()
    {
        return $this->allMethods;
    }

    /**
     * Get the static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function staticMethods()
    {
        return $this->staticMethods;
    }

    /**
     * Get the instance methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Get the public static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function publicStaticMethods()
    {
        return $this->publicStaticMethods;
    }

    /**
     * Get the public instance methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function publicMethods()
    {
        return $this->publicMethods;
    }

    /**
     * Get the protected static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function protectedStaticMethods()
    {
        return $this->protectedStaticMethods;
    }

    /**
     * Get the protected instance methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function protectedMethods()
    {
        return $this->protectedMethods;
    }

    /**
     * Get the trait methods.
     *
     * @return array<integer,ReflectionMethod> The trait methods.
     */
    public function traitMethods()
    {
        return $this->traitMethods;
    }

    private $allMethods;
    private $traitMethods;
    private $staticMethods;
    private $methods;
    private $publicStaticMethods;
    private $publicMethods;
    private $protectedStaticMethods;
    private $protectedMethods;
}
