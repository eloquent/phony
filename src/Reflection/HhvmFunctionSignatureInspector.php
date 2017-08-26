<?php

declare(strict_types=1);

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Reflection;

use Eloquent\Phony\Invocation\InvocableInspector;
use ReflectionFunctionAbstract;

/**
 * Inspects functions to determine their signature under HHVM.
 *
 * @codeCoverageIgnore
 */
class HhvmFunctionSignatureInspector extends FunctionSignatureInspector
{
    /**
     * Construct a new function signature inspector.
     *
     * @param InvocableInspector $invocableInspector The invocable inspector to use.
     * @param FeatureDetector    $featureDetector    The feature detector to use.
     */
    public function __construct(
        InvocableInspector $invocableInspector,
        FeatureDetector $featureDetector
    ) {
        parent::__construct($invocableInspector);

        $this->isIterableTypeHintSupported = $featureDetector
            ->isSupported('type.iterable');
        $this->isObjectTypeHintSupported = $featureDetector
            ->isSupported('type.object');
    }

    /**
     * Get the function signature of the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return array<string,array<string>> The function signature.
     */
    public function signature(ReflectionFunctionAbstract $function): array
    {
        $signature = [];

        foreach ($function->getParameters() as $parameter) {
            $name = $parameter->getName();

            if ($typehint = $parameter->getTypehintText()) {
                switch ($typehint) {
                    case 'array':
                    case 'callable':
                        $typehint .= ' ';

                        break;

                    case 'iterable':
                        if ($this->isIterableTypeHintSupported) {
                            $typehint .= ' ';
                        } else {
                            $typehint = '\\' . $typehint . ' ';
                        }

                        break;

                    case 'object':
                        if ($this->isObjectTypeHintSupported) {
                            $typehint .= ' ';
                        } else {
                            $typehint = '\\' . $typehint . ' ';
                        }

                        break;

                    default:
                        $typehint = '\\' . $typehint . ' ';
                }
            }

            $byReference = $parameter->isPassedByReference() ? '&' : '';

            if ($parameter->isVariadic()) {
                $variadic = '...';
            } else {
                $variadic = '';
            }

            $defaultValue = $parameter->getDefaultValueText();

            if ('' !== $defaultValue) {
                if ('NULL' === $defaultValue) {
                    $defaultValue = ' = null';
                } elseif (
                    'PHP_INT_MAX' === $defaultValue ||
                    'PHP_INT_MIN' === $defaultValue
                ) {
                    $typehint = '';
                    $defaultValue = ' = ' . $defaultValue;
                } else {
                    $defaultValue = eval('return ' . $defaultValue . ';');

                    if ('\HH\float ' === $typehint) {
                        $defaultValue = sprintf(' = %f', $defaultValue);
                    } else {
                        $defaultValue = ' = ' . var_export($defaultValue, true);
                    }
                }
            }

            $signature[$name] =
                [$typehint, $byReference, $variadic, $defaultValue];
        }

        return $signature;
    }

    private $isIterableTypeHintSupported;
    private $isObjectTypeHintSupported;
}
