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

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use ReflectionException;
use ReflectionFunctionAbstract;

/**
 * Inspects functions to determine their signature.
 *
 * @internal
 */
class FunctionSignatureInspector implements FunctionSignatureInspectorInterface
{
    /**
     * Get the static instance of this inspector.
     *
     * @return FunctionSignatureInspectorInterface The static inspector.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new function signature inspector.
     *
     * @param FeatureDetectorInterface|null $featureDetector The feature detector to use.
     */
    public function __construct(
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (null === $featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        $this->featureDetector = $featureDetector;
        $this->isCallableTypeHintSupported =
            $featureDetector->isSupported('parameter.type.callable');
        $this->isDefaultValueConstantSupported =
            $featureDetector->isSupported('parameter.default.constant');
    }

    /**
     * Get the feature detector.
     *
     * @return FeatureDetectorInterface The feature detector.
     */
    public function featureDetector()
    {
        return $this->featureDetector;
    }

    /**
     * Get the function signature of the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return array<string,array<integer,string>> The function signature.
     */
    public function signature(ReflectionFunctionAbstract $function)
    {
        $signature = array();

        foreach ($function->getParameters() as $parameter) {
            if ($parameter->isOptional()) {
                if ($parameter->isDefaultValueAvailable()) {
                    if (
                        $this->isDefaultValueConstantSupported &&
                        $parameter->isDefaultValueConstant()
                    ) {
                        $defaultValue = ' = \\' .
                            $parameter->getDefaultValueConstantName();

                        if (0 === strpos($defaultValue, ' = \self:')) {
                            $defaultValue =
                                ' = \\' .
                                $parameter->getDeclaringClass()->getName() .
                                substr($defaultValue, 8);
                        }
                    } else {
                        $defaultValue = ' = ' .
                            $this->renderValue($parameter->getDefaultValue());
                    }
                } else {
                    $defaultValue = ' = null';
                }
            } else {
                $defaultValue = '';
            }

            if ($parameter->isArray()) {
                $typeHint = 'array ';
            } elseif (
                $this->isCallableTypeHintSupported &&
                $parameter->isCallable()
            ) {
                $typeHint = 'callable ';
            } else {
                $typeHint = '';

                try {
                    if ($class = $parameter->getClass()) {
                        $typeHint = '\\' . $class->getName() . ' ';
                    }
                } catch (ReflectionException $e) {
                    if (
                        !$parameter->getDeclaringFunction()->isInternal() &&
                        preg_match(
                            sprintf(
                                '/Class (%s) does not exist/',
                                MockBuilder::SYMBOL_PATTERN
                            ),
                            $e->getMessage(),
                            $matches
                        )
                    ) {
                        $typeHint = '\\' . $matches[1] . ' ';
                    }
                }
            }

            $signature[$parameter->getName()] = array(
                $typeHint,
                $parameter->isPassedByReference() ? '&' : '',
                $defaultValue,
            );
        }

        return $signature;
    }

    /**
     * Render the supplied value.
     *
     * This method does not support recursive values, which will result in an
     * infinite loop.
     *
     * @param mixed $value The value.
     *
     * @return string The rendered value.
     */
    protected function renderValue($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (is_array($value)) {
            $values = array();

            foreach ($value as $key => $subValue) {
                $values[] = var_export($key, true) .
                    ' => ' .
                    $this->renderValue($subValue);
            }

            return 'array(' . implode(', ', $values) . ')';
        }

        return var_export($value, true);
    }

    private static $instance;
    private $featureDetector;
    private $isCallableTypeHintSupported;
    private $isDefaultValueConstantSupported;
}
