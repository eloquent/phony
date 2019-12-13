<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use Eloquent\Phony\Reflection\Exception\UndefinedFeatureException;
use ReflectionClass;

/**
 * Detects support for language features in the current runtime environment.
 */
class FeatureDetector
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
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
     * @param ?array<string,callable> $features  The features.
     * @param array<string,bool>      $supported The known feature support.
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
    public function addFeature(string $feature, callable $callback): void
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
            'reflection.reference' => function () {
                /** @var class-string */
                $className = 'ReflectionReference';

                if (class_exists($className, false)) {
                    $class = new ReflectionClass($className);

                    return $class->isInternal();
                }

                return false;
            },

            'stdout.ansi' => function () {
                // @codeCoverageIgnoreStart
                if (DIRECTORY_SEPARATOR === '\\') {
                    return
                        0 >= version_compare(
                            '10.0.10586',
                            constant('PHP_WINDOWS_VERSION_MAJOR') .
                            '.' . constant('PHP_WINDOWS_VERSION_MINOR') .
                            '.' . constant('PHP_WINDOWS_VERSION_BUILD')
                        ) ||
                        false !== getenv('ANSICON') ||
                        'ON' === getenv('ConEmuANSI') ||
                        'xterm' === getenv('TERM') ||
                        false !== getenv('BABUN_HOME');
                }
                // @codeCoverageIgnoreEnd

                return function_exists('posix_isatty') && @posix_isatty(STDOUT);
            },
        ];
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var array<string,callable>
     */
    private $features;

    /**
     * @var array<string,bool>
     */
    private $supported;
}
