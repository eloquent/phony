<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use ReflectionFunctionAbstract;

/**
 * The interface implemented by function signature inspectors.
 */
interface FunctionSignatureInspector
{
    /**
     * Get the function signature of the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return array<string,array<string>> The function signature.
     */
    public function signature(ReflectionFunctionAbstract $function): array;
}
