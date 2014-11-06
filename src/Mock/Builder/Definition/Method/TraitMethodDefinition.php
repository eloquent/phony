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

use ReflectionClass;
use ReflectionMethod;

/**
 * Represents a trait method definition.
 *
 * @internal
 */
class TraitMethodDefinition extends RealMethodDefinition implements
    TraitMethodDefinitionInterface
{
    /**
     * Construct a new trait method definition.
     *
     * @param ReflectionClass  $type   The trait type.
     * @param ReflectionMethod $method The method.
     * @param string|null      $name   The name.
     */
    public function __construct(
        ReflectionClass $type,
        ReflectionMethod $method,
        $name = null
    ) {
        $this->type = $type;

        parent::__construct($method, $name);
    }

    /**
     * Get the trait type.
     *
     * @return ReflectionClass The trait type.
     */
    public function type()
    {
        return $this->type;
    }

    private $type;
}
