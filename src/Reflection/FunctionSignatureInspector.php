<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use ReflectionClass;
use ReflectionFunctionAbstract;

/**
 * Inspects functions to determine their signature under PHP.
 */
class FunctionSignatureInspector
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

    const PARAMETER_PATTERN = '/^\s*Parameter #\d+ \[ <(required|optional)> (\?)?(\S+ )?(or NULL )?(&)?(?:\.\.\.)?\$(\S+)( = [^$]+)? ]$/m';

    /**
     * Get the function signature of the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return array<string,array<int,string>> The function signature.
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

            $typehint = $match[3];

            switch ($typehint) {
                case 'mixed ':
                    $typehint = '';

                    break;

                case '':
                case 'array ':
                case 'bool ':
                case 'callable ':
                case 'float ':
                case 'int ':
                case 'iterable ':
                case 'object ':
                case 'string ':
                    break;

                case 'boolean ':
                    $typehint = 'bool ';

                    break;

                case 'integer ':
                    $typehint = 'int ';

                    break;

                case 'self ':
                    /** @var ReflectionClass<object> */
                    $declaringClass = $parameter->getDeclaringClass();
                    $typehint = '\\' . $declaringClass->getName() . ' ';

                    break;

                default:
                    $typehint = '\\' . $typehint;
            }

            $byReference = $match[5];
            $isVariadic = $parameter->isVariadic();

            if ($isVariadic) {
                $variadic = '...';
                $optional = false;

                if ($match[2] || $match[4]) {
                    $typehint = '?' . $typehint;
                }
            } else {
                $variadic = '';
                $optional = 'optional' === $match[1];
            }

            if (isset($match[7])) {
                if (' = null' === $match[7] || ' = NULL' === $match[7]) {
                    $defaultValue = ' = null';
                } else {
                    $defaultValue = ' = ' .
                        var_export($parameter->getDefaultValue(), true);
                }
            } elseif (!$isVariadic && ($optional || $match[2] || $match[4])) {
                $defaultValue = ' = null';
            } else {
                $defaultValue = '';
            }

            /**
             * @var string
             */
            $name = $match[6];
            $signature[$name] =
                [$typehint, $byReference, $variadic, $defaultValue];
        }

        return $signature;
    }

    /**
     * @var ?self
     */
    private static $instance;
}
