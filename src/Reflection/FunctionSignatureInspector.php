<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Reflection;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use ReflectionFunctionAbstract;

/**
 * Inspects functions to determine their signature.
 */
class FunctionSignatureInspector implements FunctionSignatureInspectorInterface
{
    const PARAMETER_PATTERN = '/^\s*Parameter #\d+ \[ <(required|optional)> (\S+ )?(or NULL )?(&)?(?:\.\.\.)?\$(\S+)( = [^\]]+)? ]$/m';

    /**
     * Get the static instance of this inspector.
     *
     * @return FunctionSignatureInspectorInterface The static inspector.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new function signature inspector.
     *
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     * @param FeatureDetectorInterface|null    $featureDetector    The feature detector to use.
     */
    public function __construct(
        InvocableInspectorInterface $invocableInspector = null,
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (!$invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }
        if (!$featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        $this->invocableInspector = $invocableInspector;
        $this->featureDetector = $featureDetector;
        $this->isExportDefaultArraySupported = $featureDetector
            ->isSupported('reflection.function.export.default.array');
        $this->isExportReferenceSupported = $featureDetector
            ->isSupported('reflection.function.export.reference');
        $this->isVariadicParameterSupported = $featureDetector
            ->isSupported('parameter.variadic');
        $this->isScalarTypeHintSupported = $featureDetector
            ->isSupported('parameter.hint.scalar');
        $this->isHhvm = $featureDetector->isSupported('runtime.hhvm');
    }

    /**
     * Get the invocable inspector.
     *
     * @return InvocableInspectorInterface The invocable inspector.
     */
    public function invocableInspector()
    {
        return $this->invocableInspector;
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
     * @return array<string,array<string>> The function signature.
     */
    public function signature(ReflectionFunctionAbstract $function)
    {
        $isMatch = preg_match_all(
            static::PARAMETER_PATTERN,
            $function,
            $matches,
            PREG_SET_ORDER
        );

        if (!$isMatch) {
            return array();
        }

        $parameters = $function->getParameters();
        $signature = array();
        $index = -1;

        foreach ($matches as $match) {
            $parameter = $parameters[++$index];

            $typehint = $match[2];

            if ($this->isHhvm) {
                // @codeCoverageIgnoreStart
                if (false !== strpos($typehint, 'HH\\')) {
                    $typehint = '';
                } elseif ('?' === $typehint[0]) {
                    $typehint = substr($typehint, 1);
                }
                // @codeCoverageIgnoreEnd
            }

            if ('self ' === $typehint) {
                $typehint = '\\' . $parameter->getDeclaringClass()->getName()
                    . ' ';
            } elseif (
                '' !== $typehint &&
                'array ' !== $typehint &&
                'callable ' !== $typehint
            ) {
                if (!$this->isScalarTypeHintSupported) {
                    $typehint = '\\' . $typehint; // @codeCoverageIgnore
                } elseif (
                    'integer ' === $typehint &&
                    $parameter->getType()->isBuiltin()
                ) {
                    $typehint = 'int ';
                } elseif (
                    'boolean ' === $typehint &&
                    $parameter->getType()->isBuiltin()
                ) {
                    $typehint = 'bool ';
                } elseif ('float ' !== $typehint && 'string ' !== $typehint) {
                    $typehint = '\\' . $typehint;
                }
            }

            if ($this->isExportReferenceSupported) {
                $byReference = $match[4];
            } else {
                $byReference = $parameter->isPassedByReference() ? '&' : ''; // @codeCoverageIgnore
            }

            if (
                $this->isVariadicParameterSupported &&
                $parameter->isVariadic()
            ) {
                $variadic = '...';
                $optional = false;
            } else {
                $variadic = '';
                $optional = 'optional' === $match[1];
            }

            if (isset($match[6])) {
                if (
                    !$this->isExportDefaultArraySupported &&
                    ' = Array' === $match[6]
                ) {
                    $defaultValue = ' = ' .
                        var_export($parameter->getDefaultValue(), true);
                } else {
                    $defaultValue = $match[6];
                }

                switch ($defaultValue) {
                    case ' = NULL':
                        $defaultValue = ' = null';

                        break;

                    default:
                        $defaultValue =
                            str_replace('array (', 'array(', $defaultValue);
                }
            } elseif (
                $optional ||
                $match[3] ||
                ($this->isHhvm && $parameter->isDefaultValueAvailable())
            ) {
                $defaultValue = ' = null';
            } else {
                $defaultValue = '';
            }

            $signature[$match[5]] =
                array($typehint, $byReference, $variadic, $defaultValue);
        }

        return $signature;
    }

    /**
     * Get the function signature of the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return array<string,array<string>> The callback signature.
     */
    public function callbackSignature($callback)
    {
        return $this->signature(
            $this->invocableInspector->callbackReflector($callback)
        );
    }

    private static $instance;
    private $invocableInspector;
    private $featureDetector;
    private $isExportDefaultArraySupported;
    private $isExportReferenceSupported;
    private $isScalarTypeHintSupported;
    private $isHhvm;
}
