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
            'closure' => function ($detector) {
                return $detector->checkInternalClass('Closure');
            },

            'closure.bind' => function ($detector) {
                return $detector->checkInternalMethod('Closure', 'bind');
            },

            'constant.array' => function ($detector) {
                // syntax causes fatal on PHP < 5.6
                if ($detector->isSupported('runtime.php')) {
                    if (!$detector->checkMinimumVersion(PHP_VERSION, '5.6')) {
                        return false; // @codeCoverageIgnore
                    }
                }

                // syntax causes fatal on HHVM < 3.4
                // @codeCoverageIgnoreStart
                if ($detector->isSupported('runtime.hhvm')) {
                    if (!$detector->checkMinimumVersion(HHVM_VERSION, '3.4')) {
                        return false;
                    }
                } // @codeCoverageIgnoreEnd

                return $detector->checkStatement(
                    sprintf('const %s=array()', $detector->uniqueSymbolName()),
                    false
                );
            },

            'constant.class.array' => function ($detector) {
                // syntax causes fatal on PHP < 5.6
                if ($detector->isSupported('runtime.php')) {
                    if (!$detector->checkMinimumVersion(PHP_VERSION, '5.6')) {
                        return false; // @codeCoverageIgnore
                    }
                }

                // syntax causes fatal on HHVM < 3.4
                // @codeCoverageIgnoreStart
                if ($detector->isSupported('runtime.hhvm')) {
                    if (!$detector->checkMinimumVersion(HHVM_VERSION, '3.4')) {
                        return false;
                    }
                } // @codeCoverageIgnoreEnd

                return $detector->checkStatement(
                    sprintf(
                        'class %s{const A=array();}',
                        $detector->uniqueSymbolName()
                    ),
                    false
                );
            },

            'constant.class.expression' => function ($detector) {
                return $detector->checkStatement(
                    sprintf(
                        'class %s{const A=0+0;}',
                        $detector->uniqueSymbolName()
                    ),
                    false
                );
            },

            'constant.expression' => function ($detector) {
                return $detector->checkStatement(
                    sprintf('const %s=0+0', $detector->uniqueSymbolName()),
                    false
                );
            },

            'generator' => function ($detector) {
                return $detector->checkInternalClass('Generator');
            },

            'generator.yield' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('yield 0');
            },

            'generator.yield.assign' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('$x=yield 0');
            },

            'generator.yield.assign.key' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('$x=yield 0=>0');
            },

            'generator.yield.assign.nothing' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('$x=yield');
            },

            'generator.yield.expression' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('(yield 0)');
            },

            'generator.yield.expression.assign' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('$x=(yield 0)');
            },

            'generator.yield.expression.assign.key' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('$x=(yield 0=>0)');
            },

            'generator.yield.expression.assign.nothing' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('$x=(yield)');
            },

            'generator.yield.expression.key' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('(yield 0=>0)');
            },

            'generator.yield.expression.nothing' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('(yield)');
            },

            'generator.yield.key' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('yield 0=>0');
            },

            'generator.yield.nothing' => function ($detector) {
                return $detector->isSupported('generator') &&
                    $detector->checkStatement('yield');
            },

            'parameter.default.constant' => function ($detector) {
                return $detector->checkInternalMethod(
                    'ReflectionParameter',
                    'isDefaultValueConstant'
                );
            },

            'parameter.type.callable' => function ($detector) {
                return $detector
                    ->checkInternalMethod('ReflectionParameter', 'isCallable');
            },

            'runtime.hhvm' => function ($detector) {
                return 'hhvm' === $detector->runtime();
            },

            'runtime.php' => function ($detector) {
                return 'php' === $detector->runtime();
            },

            'trait' => function ($detector) {
                return $detector
                    ->checkInternalMethod('ReflectionClass', 'isTrait');
            },
        );
    }

    /**
     * Determine the current PHP runtime.
     *
     * @return string The runtime.
     */
    public function runtime()
    {
        if (null === $this->runtime) {
            if (
                false === strpos(
                    $this->captureOutput('phpinfo', array(0)),
                    'HipHop'
                )
            ) {
                $this->runtime = 'php';
            } else {
                $this->runtime = 'hhvm';
            }
        }

        return $this->runtime;
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
    public function checkStatement($source, $useClosure = null)
    {
        if (null === $useClosure) {
            $useClosure = true;
        }

        if ($useClosure) {
            return true === @eval(
                sprintf('function () {%s;};return true;', $source)
            );
        }

        return true === @eval(sprintf('%s;return true;', $source));
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

    /**
     * Check that the specified version is greater than or equal to a given
     * version.
     *
     * This method uses simple version numbers like '5.6' or '5.4.3'.
     *
     * @param string $version The version.
     * @param string $minimum The minimum version.
     *
     * @return boolean True if the version matches.
     */
    public function checkMinimumVersion($version, $minimum)
    {
        if (true === $minimum) {
            return true;
        }
        if (false === $minimum) {
            return false;
        }

        return version_compare($version, $this->minimumVersion($minimum), '>');
    }

    /**
     * Check that the specified version is less than or equal to a given
     * version.
     *
     * This method uses simple version numbers like '5.6' or '5.4.3'.
     *
     * @param string $version The version.
     * @param string $maximum The maximum version.
     *
     * @return boolean True if the version matches.
     */
    public function checkMaximumVersion($version, $maximum)
    {
        if (true === $maximum) {
            return true;
        }
        if (false === $maximum) {
            return false;
        }

        return version_compare($version, $this->maximumVersion($maximum), '<=');
    }

    /**
     * Capture the output produced by a callback.
     *
     * @param callable                  $callback  The callback.
     * @param array<integer,mixed>|null $arguments Arguments to pass to the callback.
     *
     * @return string The captured output.
     */
    public function captureOutput($callback, array $arguments = null)
    {
        if (null === $arguments) {
            $arguments = array();
        }

        ob_start();
        call_user_func_array($callback, $arguments);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Returns a symbol name that is unique for this process execution.
     *
     * @return string The symbol name.
     */
    public function uniqueSymbolName()
    {
        return sprintf('_FD_symbol_%s', md5(uniqid()));
    }

    private function minimumVersion($minimum)
    {
        $parts = explode('.', $minimum);
        $partCount = count($parts);
        $parts[$partCount - 1] = strval(intval($parts[$partCount - 1]) - 1);
        $parts[] = '99999';

        return implode('.', $parts);
    }

    private function maximumVersion($maximum)
    {
        return $maximum . '.99999';
    }

    private static $instance;
    private $features;
    private $supported;
    private $runtime;
}
