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

/**
 * The interface implemented by trait method definitions.
 */
interface TraitMethodDefinitionInterface extends MethodDefinitionInterface
{
    /**
     * Get the trait type.
     *
     * @return ReflectionClass The trait type.
     */
    public function type();
}
