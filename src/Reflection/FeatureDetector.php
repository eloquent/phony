<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use Eloquent\Phony\Reflection\Exception\UndefinedFeatureException;
use ReflectionException;
use ReflectionFunction;
use Throwable;

/**
 * Detects support for language features in the current runtime environment.
 */
class FeatureDetector
{
    /**
     * Get the static instance of this detector.
     *
     * @return FeatureDetector The static detector.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new feature detector.
     *
     * @param array<string,callable>|null $features  The features.
     * @param array<string,bool>          $supported The known feature support.
     */
    public function __construct(
        array $features = null,
        array $supported = []
    ) {
        if (null === $features) {
            $features = $this->standardFeatures();
        }

        $this->features = $features;
        $this->supported = $supported;

        $this->isErrorClearLastSupported = function_exists('error_clear_last');

        // @codeCoverageIgnoreStart
        $this->nullErrorHandler = function () {
            return false;
        };
        // @codeCoverageIgnoreEnd
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
    public function addFeature(string $feature, callable $callback)
    {
        $this->features[$feature] = $callback;
    }

    /**
     * Get the features.
     *
     * @return array<string,callable> The features.
     */
    public function features(): array
    {
        return $this->features;
    }

    /**
     * Get the known feature support.
     *
     * @return array<string,bool> The known feature support.
     */
    public function supported(): array
    {
        return $this->supported;
    }

    /**
     * Returns true if the specified feature is supported by the current
     * runtime environment.
     *
     * @param string $feature The feature.
     *
     * @return bool                      True if supported.
     * @throws UndefinedFeatureException If the specified feature is undefined.
     */
    public function isSupported(string $feature): bool
    {
        if (!array_key_exists($feature, $this->supported)) {
            if (!isset($this->features[$feature])) {
                throw new UndefinedFeatureException($feature);
            }

            $this->supported[$feature] =
                (bool) $this->features[$feature]($this);
        }

        return $this->supported[$feature];
    }

    /**
     * Get the standard feature detection callbacks.
     *
     * @return array<string,callable> The standard features.
     */
    public function standardFeatures(): array
    {
        return [
            'stdout.ansi' => function () {
                // @codeCoverageIgnoreStart
                if (DIRECTORY_SEPARATOR === '\\') {
                    return
                        0 >= version_compare(
                        '10.0.10586',
                        PHP_WINDOWS_VERSION_MAJOR .
                            '.' . PHP_WINDOWS_VERSION_MINOR .
                            '.' . PHP_WINDOWS_VERSION_BUILD
                        ) ||
                        false !== getenv('ANSICON') ||
                        'ON' === getenv('ConEmuANSI') ||
                        'xterm' === getenv('TERM') ||
                        false !== getenv('BABUN_HOME');
                }
                // @codeCoverageIgnoreEnd

                return function_exists('posix_isatty') && @posix_isatty(STDOUT);
            },

            'type.iterable' => function () {
                try {
                    $function =
                        new ReflectionFunction(function (iterable $a) {});
                    $parameters = $function->getParameters();
                    $result = null === $parameters[0]->getClass();
                    // @codeCoverageIgnoreStart
                } catch (ReflectionException $e) {
                    $result = false;
                }
                // @codeCoverageIgnoreEnd

                return $result;
            },

            'type.nullable' => function ($detector) {
                return $detector->checkStatement('function(?int $a){}');
            },

            'type.object' => function () {
                try {
                    $function =
                        new ReflectionFunction(function (object $a) {});
                    $parameters = $function->getParameters();
                    $result = null === $parameters[0]->getClass();
                    // @codeCoverageIgnoreStart
                } catch (ReflectionException $e) {
                    $result = false;
                }
                // @codeCoverageIgnoreEnd

                return $result;
            },

            'type.void' => function ($detector) {
                return $detector->checkStatement(
                    '$r=new ReflectionFunction(function():void{});' .
                        'return $r->getReturnType()->isBuiltin();'
                );
            },
        ];
    }

    /**
     * Check that the supplied syntax is valid.
     *
     * @param string $source The source to check.
     *
     * @return bool True if the syntax is valid.
     */
    public function checkStatement(string $source): bool
    {
        $reporting = error_reporting(E_ERROR | E_COMPILE_ERROR);
        $result = false;

        try {
            $result = eval(sprintf('%s;return true;', $source));
        } catch (Throwable $e) {
            // intentionally silenced
        }

        if (false === $result) {
            if ($this->isErrorClearLastSupported) {
                error_clear_last();
                // @codeCoverageIgnoreStart
            } else {
                set_error_handler($this->nullErrorHandler);
                @trigger_error('');
                restore_error_handler();
            }
            // @codeCoverageIgnoreEnd
        }

        error_reporting($reporting);

        return true === $result;
    }

    private static $instance;
    private $features;
    private $supported;
    private $isErrorClearLastSupported;
    private $nullErrorHandler;
}
