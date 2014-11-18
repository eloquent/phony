<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Reflection;

use ReflectionFunctionAbstract;

/**
 * The interface implemented by function signature inspectors.
 *
 * @internal
 */
interface FunctionSignatureInspectorInterface
{
    /**
     * Get the function signature of the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return array<string,array<integer,string>> The function signature.
     */
    public function signature(ReflectionFunctionAbstract $function);

    /**
     * Get the function signature of the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return array<string,array<integer,string>> The callback signature.
     */
    public function callbackSignature($callback);
}
