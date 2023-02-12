<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Inspects functions to determine their signature under PHP.
 *
 * The implementation here is much faster than using reflection due to reduced
 * function call overhead. Phony needs to inspect a lot of functions, so this
 * has a pretty significant impact on overall performance.
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

    /**
     * Matches the parameter information in the result of casting a
     * ReflectionFunctionAbstract to a string.
     *
     * Prefix ------------------------------------------------------------------
     *
     *   Parameter #\d+ \[
     *
     * "Optional" flag ---------------------------------------------------------
     *
     *   (?:<required>|(<optional>)?)
     *
     *   Capture group 1:
     *     Non-empty if the argument is optional
     *
     *   For some built-in methods, this used to be the only indication that an
     *   argument was optional. Seems to be fixed in PHP 8, but still required
     *   for PHP 7.
     *
     * Type definition ---------------------------------------------------------
     *
     *   (?:(\?)?(\S+) (or NULL )?)?
     *
     *   Capture group 2:
     *     Non-empty if the type is nullable (PHP 8)
     *
     *   Capture group 3:
     *     Contains the type definition itself
     *
     *   Capture group 4:
     *     Non-empty if the type is nullable (PHP 7)
     *
     * By-reference arguments --------------------------------------------------
     *
     *   ?(&)?
     *
     *   Capture group 5:
     *     Non-empty if the argument is by-reference
     *
     * Variadic arguments ------------------------------------------------------
     *
     *   (\.{3})?
     *
     *   Capture group 6:
     *     Non-empty if the argument is by-variadic
     *
     * Argument name -----------------------------------------------------------
     *
     *   \$(\S+)
     *
     *   Capture group 7:
     *     Contains the argument name (without the "$" symbol)
     *
     * Default value -----------------------------------------------------------
     *
     *   ((?: = \S+)?)
     *
     *   Capture group 8:
     *     Contains a string representation the default value
     *
     *   Matches the default value. Can only be trusted for default values of
     *   "null", otherwise the *actual* default must be read via reflection.
     *
     *   It's expressed as a capturing group around a non-capturing group,
     *   because otherwise PHP will leave this group's offset completely
     *   undefined in the match array.
     */
    const PARAMETER_PATTERN = '/Parameter #\d+ \[ (?:<required>|(<optional>)?) (?:(\?)?(\S+) (or NULL )?)?(&)?(\.{3})?\$(\S+)((?: = \S+)?)/';

    /**
     * Matches the return tyoe information in the result of casting a
     * ReflectionFunctionAbstract to a string.
     *
     * Prefix ------------------------------------------------------------------
     *
     *   Return \[
     *
     * Type definition ---------------------------------------------------------
     *
     *   (\?)?(\S+) ((?:or NULL )?)
     *
     *   Capture group 1:
     *     Non-empty if the type is nullable (PHP 8)
     *
     *   Capture group 2:
     *     Contains the type definition itself
     *
     *   Capture group 3:
     *     Non-empty if the type is nullable (PHP 7)
     */
    const RETURN_PATTERN = '/Return \[ (\?)?(\S+) ((?:or NULL )?)/';

    /**
     * Get the function signature of the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return array{0:array<string,array<int,string>>,1:string} The function signature.
     */
    public function signature(ReflectionFunctionAbstract $function): array
    {
        $functionString = (string) $function;
        $hasReturnType = preg_match(
            static::RETURN_PATTERN,
            $functionString,
            $returnMatches
        );
        $hasParameters = preg_match_all(
            static::PARAMETER_PATTERN,
            $functionString,
            $parameterMatches,
            PREG_SET_ORDER
        );

        $returnType = '';

        if ($hasReturnType) {
            /**
             * @var string $isNullablePhp8
             * @var string $typeReference
             * @var string $isNullablePhp7
             */
            list(,
                $isNullablePhp8,
                $typeReference,
                $isNullablePhp7,
            ) = $returnMatches;

            $subTypes = explode(self::UNION, $typeReference);

            foreach ($subTypes as $subType) {
                if ($returnType) {
                    $returnType .= self::UNION;
                }

                switch ($subType) {
                    case 'array':
                    case 'bool':
                    case 'callable':
                    case 'false':
                    case 'float':
                    case 'int':
                    case 'iterable':
                    case 'mixed':
                    case 'null':
                    case 'object':
                    case 'static':
                    case 'string':
                    case 'void':
                        $returnType .= $subType;

                        break;

                    case 'self':
                        /** @var ReflectionMethod */
                        $method = $function;
                        /** @var ReflectionClass<object> */
                        $declaringClass = $method->getDeclaringClass();
                        $returnType .= self::NS . $declaringClass->getName();

                        break;

                    case 'parent':
                        /** @var ReflectionMethod */
                        $method = $function;
                        /** @var ReflectionClass<object> */
                        $declaringClass = $method->getDeclaringClass();
                        /** @var ReflectionClass<object> */
                        $parentClass = $declaringClass->getParentClass();
                        $returnType .= self::NS . $parentClass->getName();

                        break;

                    default:
                        $returnType .= self::NS . $subType;
                }
            }

            if ($isNullablePhp7 || $isNullablePhp8) {
                $returnType = '?' . $returnType;
            }
        }

        $signature = [[], $returnType];

        if (!$hasParameters) {
            return $signature;
        }

        $parameters = null;
        $index = -1;

        foreach ($parameterMatches as $match) {
            ++$index;

            /**
             * @var string $isOptional
             * @var string $isNullablePhp8
             * @var string $typeReference
             * @var string $isNullablePhp7
             * @var string $byReference
             * @var string $variadic
             * @var string $name
             * @var string $defaultValue
             */
            list(,
                $isOptional,
                $isNullablePhp8,
                $typeReference,
                $isNullablePhp7,
                $byReference,
                $variadic,
                $name,
                $defaultValue,
            ) = $match;

            $type = '';

            if ($typeReference && 'mixed' !== $typeReference) {
                $subTypes = explode(self::UNION, $typeReference);

                foreach ($subTypes as $subType) {
                    if ($type) {
                        $type .= self::UNION;
                    }

                    switch ($subType) {
                        case '':
                        case 'array':
                        case 'bool':
                        case 'callable':
                        case 'false':
                        case 'float':
                        case 'int':
                        case 'iterable':
                        case 'mixed':
                        case 'null':
                        case 'object':
                        case 'string':
                            $type .= $subType;

                            break;

                        case 'self':
                            if (!$parameters) {
                                $parameters = $function->getParameters();
                            }

                            $parameter = $parameters[$index];

                            /** @var ReflectionClass<object> */
                            $declaringClass = $parameter->getDeclaringClass();
                            $type .= self::NS . $declaringClass->getName();

                            break;

                        case 'parent':
                            if (!$parameters) {
                                $parameters = $function->getParameters();
                            }

                            $parameter = $parameters[$index];

                            /** @var ReflectionClass<object> */
                            $declaringClass = $parameter->getDeclaringClass();
                            /** @var ReflectionClass<object> */
                            $parentClass = $declaringClass->getParentClass();
                            $type .= self::NS . $parentClass->getName();

                            break;

                        default:
                            $type .= self::NS . $subType;
                    }
                }
            }

            if ($type) {
                $type .= ' ';

                if ($isNullablePhp7 || $isNullablePhp8) {
                    $type = '?' . $type;
                }
            }

            if ($defaultValue) {
                if ($defaultValue === ' = NULL') {
                    $defaultValue = self::DEFAULT_NULL;
                } elseif ($defaultValue !== self::DEFAULT_NULL) {
                    if (!$parameters) {
                        $parameters = $function->getParameters();
                    }

                    $parameter = $parameters[$index];

                    try {
                        $realDefaultValue = $parameter->getDefaultValue();
                    } catch (ReflectionException $e) {
                        $realDefaultValue = null;
                    }

                    if (null === $realDefaultValue) {
                        $defaultValue = self::DEFAULT_NULL;
                    } else {
                        $defaultValue =
                            ' = ' . var_export($realDefaultValue, true);
                    }
                }
            } elseif ($isOptional && !$variadic) {
                $defaultValue = self::DEFAULT_NULL;
            }

            $signature[0][$name] =
                [$type, $byReference, $variadic, $defaultValue];
        }

        return $signature;
    }

    const DEFAULT_NULL = ' = null';
    const NS = '\\';
    const UNION = '|';

    /**
     * @var ?self
     */
    private static $instance;
}
