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
 * Represents a collection of methods.
 *
 * @internal
 */
class MethodDefinitionCollection implements MethodDefinitionCollectionInterface
{
    /**
     * A comparator for sorting method definitions.
     *
     * @param MethodDefinitionInterface $left  The left definition.
     * @param MethodDefinitionInterface $right The right definition.
     *
     * @return integer The result.
     */
    public static function compareDefinitions(
        MethodDefinitionInterface $left,
        MethodDefinitionInterface $right
    ) {
        $leftAccess = $left->accessLevel();
        $rightAccess = $right->accessLevel();

        if ($leftAccess !== $rightAccess) {
            return $leftAccess - $rightAccess;
        }

        if ($left->isStatic()) {
            if (!$right->isStatic()) {
                return -1;
            }
        } elseif ($right->isStatic()) {
            return 1;
        }

        return strcmp($left->name(), $right->name());
    }

    /**
     * Construct a new custom method definition.
     *
     * @param array<string,MethodDefinitionInterface> $methods The methods.
     */
    public function __construct(array $methods)
    {
        uasort($methods, get_class() . '::compareDefinitions');

        $this->methods = $methods;
    }

    /**
     * Get the methods.
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
        return array_filter(
            $this->methods,
            function ($method) {
                return $method->isStatic() && 0 === $method->accessLevel();
            }
        );
    }

    /**
     * Get the public non-static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function publicMethods()
    {
        return array_filter(
            $this->methods,
            function ($method) {
                return !$method->isStatic() && 0 === $method->accessLevel();
            }
        );
    }

    /**
     * Get the protected static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function protectedStaticMethods()
    {
        return array_filter(
            $this->methods,
            function ($method) {
                return $method->isStatic() && 1 === $method->accessLevel();
            }
        );
    }

    /**
     * Get the protected non-static methods.
     *
     * @return array<string,MethodDefinitionInterface> The methods.
     */
    public function protectedMethods()
    {
        return array_filter(
            $this->methods,
            function ($method) {
                return !$method->isStatic() && 1 === $method->accessLevel();
            }
        );
    }

    private $methods;
}
