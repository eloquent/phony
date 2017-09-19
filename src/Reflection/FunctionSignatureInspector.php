<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use ReflectionFunctionAbstract;

/**
 * Inspects functions to determine their signature under PHP.
 */
class FunctionSignatureInspector
{
    /**
     * Get the static instance of this inspector.
     *
     * @return PhpFunctionSignatureInspector The static inspector.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(FeatureDetector::instance());
        }

        return self::$instance;
    }

    const PARAMETER_PATTERN = '/^\s*Parameter #\d+ \[ <(required|optional)> (\S+ )?(or NULL )?(&)?(?:\.\.\.)?\$(\S+)( = [^$]+)? ]$/m';

    /**
     * Construct a new function signature inspector.
     *
     * @param FeatureDetector $featureDetector The feature detector to use.
     */
    public function __construct(FeatureDetector $featureDetector)
    {
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
        $isMatch = preg_match_all(
            static::PARAMETER_PATTERN,
            (string) $function,
            $matches,
            PREG_SET_ORDER
        );

        if (!$isMatch) {
            return [];
        }

        $parameters = $function->getParameters();
        $signature = [];
        $index = -1;

        foreach ($matches as $match) {
            $parameter = $parameters[++$index];

            $typehint = $match[2];

            if ('self ' === $typehint) {
                $typehint = '\\' . $parameter->getDeclaringClass()->getName()
                    . ' ';
            } elseif (
                '' !== $typehint &&
                'array ' !== $typehint &&
                'callable ' !== $typehint &&
                (
                    !$this->isIterableTypeHintSupported ||
                    'iterable ' !== $typehint
                ) &&
                (
                    !$this->isObjectTypeHintSupported ||
                    'object ' !== $typehint
                )
            ) {
                if (
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

            $byReference = $match[4];

            if ($parameter->isVariadic()) {
                $variadic = '...';
                $optional = false;
            } else {
                $variadic = '';
                $optional = 'optional' === $match[1];
            }

            if (isset($match[6])) {
                if (' = NULL' === $match[6]) {
                    $defaultValue = ' = null';
                } else {
                    $defaultValue = ' = ' .
                        var_export($parameter->getDefaultValue(), true);
                }
            } elseif ($optional || $match[3]) {
                $defaultValue = ' = null';
            } else {
                $defaultValue = '';
            }

            $signature[$match[5]] =
                [$typehint, $byReference, $variadic, $defaultValue];
        }

        return $signature;
    }

    private static $instance;
    private $isIterableTypeHintSupported;
    private $isObjectTypeHintSupported;
}
