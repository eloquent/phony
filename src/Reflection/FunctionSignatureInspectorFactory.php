<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

/**
 * Creates function signature inspectors.
 */
abstract class FunctionSignatureInspectorFactory
{
    /**
     * Get the static instance of the function signature inspector.
     *
     * @return FunctionSignatureInspector The static inspector.
     */
    public static function create()
    {
        if (!self::$instance) {
            $featureDetector = FeatureDetector::instance();

            if ($featureDetector->isSupported('runtime.hhvm')) {
                // @codeCoverageIgnoreStart
                self::$instance =
                    new HhvmFunctionSignatureInspector($featureDetector);
                // @codeCoverageIgnoreEnd
            } else {
                self::$instance =
                    new PhpFunctionSignatureInspector($featureDetector);
            }
        }

        return self::$instance;
    }

    private static $instance;
}
