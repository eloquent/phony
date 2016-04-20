<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Value;

use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionType;

/**
 * The interface implemented by value factories.
 */
interface ValueFactoryInterface
{
    /**
     * Create a value of the supplied type.
     *
     * @param string $typeName The type name.
     *
     * @return mixed A value of the supplied type.
     */
    public function fromTypeName($typeName);

    /**
     * Create a value that would be accepted by the supplied parameter.
     *
     * @param ReflectionParameter $paramter The parameter.
     *
     * @return mixed A value of the parameter type.
     */
    public function fromParameter(ReflectionParameter $parameter);

    /**
     * Create a value of the supplied type.
     *
     * @param ReflectionType $type The type.
     *
     * @return mixed A value of the supplied type.
     */
    public function fromType(ReflectionType $type);

    /**
     * Create a value that can be returned by the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return mixed A value of the return type.
     */
    public function fromReturnType(ReflectionFunctionAbstract $function);
}
