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
use ReflectionFunctionAbstract;

/**
 * Inspects functions to determine their signature.
 *
 * @internal
 */
class FunctionSignatureInspector implements FunctionSignatureInspectorInterface
{
    const PARAMETER_PATTERN = '/^\s*Parameter #\d+ \[ <(required|optional)> (\S+ )?(?:or NULL )?(&)?\$(\S+)( = [^\]]+)? ]$/m';

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
        $this->isExportDefaultArraySupported = $featureDetector
            ->isSupported('reflection.function.export.default.array');
        $this->isExportReferenceSupported = $featureDetector
            ->isSupported('reflection.function.export.reference');
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
        $isMatch = preg_match_all(
            static::PARAMETER_PATTERN,
            strval($function),
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

            if (isset($match[2])) {
                $typehint = $match[2];

                if (false !== strpos($typehint, 'HH\\')) {
                    $typehint = '';
                }

                switch ($typehint) {
                    case '':
                    case 'array ':
                    case 'callable ':
                    case 'self ':
                        break;

                    default:
                        $typehint = '\\' . $typehint;
                }
            } else {
                $typehint = '';
            }

            if ($this->isExportReferenceSupported) {
                $byReference = isset($match[3]) ? $match[3] : '';
            } else { // @codeCoverageIgnoreStart
                $byReference = $parameter->isPassedByReference();
            } // @codeCoverageIgnoreEnd

            if (isset($match[5])) {
                if (
                    !$this->isExportDefaultArraySupported &&
                    ' = Array' === $match[5]
                ) {
                    $defaultValue = ' = ' .
                        var_export($parameter->getDefaultValue(), true);
                } else {
                    $defaultValue = $match[5];
                }

                switch ($defaultValue) {
                    case ' = NULL':
                        $defaultValue = ' = null';
                }
            } elseif ('optional' === $match[1]) {
                $defaultValue = ' = null';
            } else {
                $defaultValue = '';
            }

            $signature[$match[4]] =
                array($typehint, $byReference, $defaultValue);
        }

        return $signature;
    }

    private static $instance;
    private $featureDetector;
    private $isExportDefaultArraySupported;
    private $isExportReferenceSupported;
}
