<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Feature;

use Eloquent\Phony\Feature\Exception\UndefinedFeatureException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Detects support for language features in the current runtime environment.
 *
 * @internal
 */
class FeatureDetector implements FeatureDetectorInterface
{
    /**
     * Get the static instance of this detector.
     *
     * @return FeatureDetectorInterface The static detector.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new feature detector.
     *
     * @param array<string,callable>|null $features  The features.
     * @param array<string,boolean>|null  $supported The known feature support.
     */
    public function __construct(array $features = null, array $supported = null)
    {
        if (null === $features) {
            $features = $this->standardFeatures();
        }
        if (null === $supported) {
            $supported = array();
        }

        $this->features = $features;
        $this->supported = $supported;
    }

    /**
     * Add a custom feature.
     *
     * The callback will be passed this detector as the first argument. The
     * return value will be interpreted as a boolean.
     *
     * @param string   $feature  The feature.
     * @param callable $callback The feature detection callback.
     */
    public function addFeature($feature, $callback)
    {
        $this->features[$feature] = $callback;
    }

    /**
     * Get the features.
     *
     * @return array<string,callable> The features.
     */
    public function features()
    {
        return $this->features;
    }

    /**
     * Get the known feature support.
     *
     * @return array<string,boolean> The known feature support.
     */
    public function supported()
    {
        return $this->supported;
    }

    /**
     * Returns true if the specified feature is supported by the current
     * runtime environment.
     *
     * @param string $feature The feature.
     *
     * @return boolean                   True if supported.
     * @throws UndefinedFeatureException If the specified feature is undefined.
     */
    public function isSupported($feature)
    {
        if (!array_key_exists($feature, $this->supported)) {
            if (!isset($this->features[$feature])) {
                throw new UndefinedFeatureException($feature);
            }

            $this->supported[$feature] =
                (boolean) call_user_func($this->features[$feature], $this);
        }

        return $this->supported[$feature];
    }

    /**
     * Get the standard feature detection callbacks.
     *
     * @return array<string,callable> The standard features.
     */
    public function standardFeatures()
    {
        return array(
            'closures' => function ($detector) {
                return $detector->checkInternalClass('Closure');
            },

            'closures.bind' => function ($detector) {
                return $detector->checkInternalMethod('Closure', 'bind');
            },

            'generators' => function ($detector) {
                return $detector->checkToken('yield', 'T_YIELD');
            },

            'generators.yield' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('yield 0');
            },

            'generators.yield.assign' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('$x=yield 0');
            },

            'generators.yield.assign.key' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('$x=yield 0=>0');
            },

            'generators.yield.assign.nothing' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('$x=yield');
            },

            'generators.yield.expression' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('(yield 0)');
            },

            'generators.yield.expression.assign' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('$x=(yield 0)');
            },

            'generators.yield.expression.assign.key' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('$x=(yield 0=>0)');
            },

            'generators.yield.expression.assign.nothing' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('$x=(yield)');
            },

            'generators.yield.expression.key' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('(yield 0=>0)');
            },

            'generators.yield.expression.nothing' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('(yield)');
            },

            'generators.yield.key' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('yield 0=>0');
            },

            'generators.yield.nothing' => function ($detector) {
                return $detector->isSupported('generators') &&
                    $detector->checkExpression('yield');
            },

            'traits' => function ($detector) {
                return $detector->checkToken('trait', 'T_TRAIT');
            },
        );
    }

    /**
     * Check that a keyword is interpreted as a particular token type.
     *
     * @param string $keyword      The keyword.
     * @param string $constantName The name of the token type constant.
     *
     * @return boolean True if the keyword is interpreted as expected.
     */
    public function checkToken($keyword, $constantName)
    {
        if (!defined($constantName)) {
            return false;
        }

        $tokens = token_get_all('<?php ' . $keyword);

        return is_array($tokens[1]) &&
            constant($constantName) === $tokens[1][0];
    }

    /**
     * Check that the supplied syntax is valid.
     *
     * @param string $source The source to check.
     *
     * @return boolean True if the syntax is valid.
     */
    public function checkExpression($source)
    {
        return true === @eval(sprintf('function () {%s;};return true;', $source));
    }

    /**
     * Check that the specified class is defined by the PHP core, or an
     * extension.
     *
     * @param string $className The class name.
     *
     * @return boolean True if the class exists, and is internal.
     */
    public function checkInternalClass($className)
    {
        if (class_exists($className, false)) {
            $class = new ReflectionClass($className);

            return $class->isInternal();
        }

        return false;
    }

    /**
     * Check that the specified method is defined by the PHP core, or an
     * extension.
     *
     * @param string $className  The class name.
     * @param string $methodName The class name.
     *
     * @return boolean True if the method exists, and is internal.
     */
    public function checkInternalMethod($className, $methodName)
    {
        if (method_exists($className, $methodName)) {
            $method = new ReflectionMethod($className, $methodName);

            return $method->isInternal();
        }

        return false;
    }

    private static $instance;
    private $features;
    private $supported;
}
