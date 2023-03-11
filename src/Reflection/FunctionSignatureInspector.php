<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
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
     * Matches the parameter information in the result of casting a
     * ReflectionFunctionAbstract to a string.
     *
     * Prefix ------------------------------------------------------------------
     *
     *   Parameter #\d+ \[ <(?:required|optional)>
     *
     * Type definition ---------------------------------------------------------
     *
     *   (?:(\?)?(\S+) )?
     *
     *   Capture group 1:
     *     Non-empty if the type is nullable
     *
     *   Capture group 2:
     *     Contains the type definition itself
     *
     * By-reference arguments --------------------------------------------------
     *
     *   ?(&)?
     *
     *   Capture group 3:
     *     Non-empty if the argument is by-reference
     *
     * Variadic arguments ------------------------------------------------------
     *
     *   (\.{3})?
     *
     *   Capture group 4:
     *     Non-empty if the argument is by-variadic
     *
     * Argument name -----------------------------------------------------------
     *
     *   \$(\S+)
     *
     *   Capture group 5:
     *     Contains the argument name (without the "$" symbol)
     *
     * Default value -----------------------------------------------------------
     *
     *   ((?: = \S+)?)
     *
     *   Capture group 6:
     *     Contains a string representation the default value
     *
     *   Matches the default value. Can only be trusted for default values of
     *   "null", otherwise the *actual* default must be read via reflection.
     *
     *   It's expressed as a capturing group around a non-capturing group,
     *   because otherwise PHP will leave this group's offset completely
     *   undefined in the match array.
     */
    const PARAMETER_PATTERN = '/Parameter #\d+ \[ <(?:required|optional)> (?:(\?)?(\S+) )?(&)?(\.{3})?\$(\S+)((?: = \S+)?)/';

    /**
     * Matches the return type information in the result of casting a
     * ReflectionFunctionAbstract to a string.
     *
     * Prefix ------------------------------------------------------------------
     *
     *   (?:Return|Tentative return) \[
     *
     * Type definition ---------------------------------------------------------
     *
     *   (\?)?(\S+)
     *
     *   Capture group 1:
     *     Non-empty if the type is nullable
     *
     *   Capture group 2:
     *     Contains the type definition itself
     */
    const RETURN_PATTERN = '/(?:Return|Tentative return) \[ (\?)?(\S+)/';

    /**
     * Get the function signature of the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return array{array<string,array<int,string>>,string} The function signature.
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
             * @var string $isNullable
             * @var string $typeReference
             */
            list(,
                $isNullable,
                $typeReference,
            ) = $returnMatches;

            $unionParts = explode(self::UNION, $typeReference);
            $isFirstUnion = true;

            foreach ($unionParts as $unionPart) {
                if ($isFirstUnion) {
                    $isFirstUnion = false;
                } else {
                    $returnType .= self::UNION;
                }

                if (self::OPEN_PAREN === $unionPart[0]) {
                    $hasParens = true;
                    $intersectionParts =
                        explode(self::INTERSECTION, substr($unionPart, 1, -1));
                } else {
                    $hasParens = false;
                    $intersectionParts = explode(self::INTERSECTION, $unionPart);
                }

                $isFirstIntersection = true;

                foreach ($intersectionParts as $subType) {
                    if ($isFirstIntersection) {
                        $isFirstIntersection = false;

                        if ($hasParens) {
                            $returnType .= self::OPEN_PAREN;
                        }
                    } else {
                        $returnType .= self::INTERSECTION;
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
                        case 'never':
                        case 'null':
                        case 'object':
                        case 'static':
                        case 'string':
                        case 'true':
                        case 'void':
                            $returnType .= $subType;

                            break;

                        case 'self':
                            /** @var ReflectionMethod */
                            $method = $function;
                            /** @var ReflectionClass<object> */
                            $declaringClass = $method->getDeclaringClass();

                            if ($declaringClass->isTrait()) {
                                $returnType .= $subType;
                            } else {
                                $returnType .=
                                    self::NS . $declaringClass->getName();
                            }

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

                if ($hasParens) {
                    $returnType .= self::CLOSE_PAREN;
                }
            }

            if ($isNullable) {
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
             * @var string $isNullable
             * @var string $typeReference
             * @var string $byReference
             * @var string $variadic
             * @var string $name
             * @var string $defaultValue
             */
            list(,
                $isNullable,
                $typeReference,
                $byReference,
                $variadic,
                $name,
                $defaultValue,
            ) = $match;

            $type = '';

            if ($typeReference && 'mixed' !== $typeReference) {
                $unionParts = explode(self::UNION, $typeReference);
                $isFirstUnion = true;

                foreach ($unionParts as $unionPart) {
                    if ($isFirstUnion) {
                        $isFirstUnion = false;
                    } else {
                        $type .= self::UNION;
                    }

                    if (self::OPEN_PAREN === $unionPart[0]) {
                        $hasParens = true;
                        $intersectionParts = explode(
                            self::INTERSECTION,
                            substr($unionPart, 1, -1)
                        );
                    } else {
                        $hasParens = false;
                        $intersectionParts =
                            explode(self::INTERSECTION, $unionPart);
                    }

                    $isFirstIntersection = true;

                    foreach ($intersectionParts as $subType) {
                        if ($isFirstIntersection) {
                            $isFirstIntersection = false;

                            if ($hasParens) {
                                $type .= self::OPEN_PAREN;
                            }
                        } else {
                            $type .= self::INTERSECTION;
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
                            case 'true':
                                $type .= $subType;

                                break;

                            case 'self':
                                if (!$parameters) {
                                    $parameters = $function->getParameters();
                                }

                                $parameter = $parameters[$index];

                                /** @var ReflectionClass<object> */
                                $declaringClass =
                                    $parameter->getDeclaringClass();

                                if ($declaringClass->isTrait()) {
                                    $type .= $subType;
                                } else {
                                    $type .=
                                        self::NS . $declaringClass->getName();
                                }

                                break;

                            case 'parent':
                                if (!$parameters) {
                                    $parameters = $function->getParameters();
                                }

                                $parameter = $parameters[$index];

                                /** @var ReflectionClass<object> */
                                $declaringClass =
                                    $parameter->getDeclaringClass();
                                /** @var ReflectionClass<object> */
                                $parentClass =
                                    $declaringClass->getParentClass();
                                $type .= self::NS . $parentClass->getName();

                                break;

                            default:
                                $type .= self::NS . $subType;
                        }
                    }

                    if ($hasParens) {
                        $type .= self::CLOSE_PAREN;
                    }
                }
            }

            if ($type) {
                $type .= ' ';

                if ($isNullable) {
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
            }

            $signature[0][$name] =
                [$type, $byReference, $variadic, $defaultValue];
        }

        return $signature;
    }

    /**
     * Get the function signature of the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return array{array<string,array<int,string>>,string} The function signature.
     */
    public function signatureFromCallback(callable $callback): array
    {
        if (is_object($callback)) {
            return $this->signature(
                new ReflectionMethod($callback, '__invoke')
            );
        }

        if (is_array($callback)) {
            list($classNameOrObject, $methodName) = $callback;

            if (str_starts_with($methodName, 'parent::')) {
                $class = new ReflectionClass($classNameOrObject);
                /** @var ReflectionClass<object> $parentClass */
                $parentClass = $class->getParentClass();

                return $this->signature(
                    new ReflectionMethod(
                        $parentClass->getName(),
                        substr($methodName, 8),
                    )
                );
            }

            return $this->signature(new ReflectionMethod(...$callback));
        }

        if (is_string($callback)) {
            if (str_contains($callback, '::')) {
                return $this->signature(new ReflectionMethod($callback));
            }

            return $this->signature(new ReflectionFunction($callback));
        }

        // @codeCoverageIgnoreStart
        throw new InvalidArgumentException(
            sprintf('Unsupported callback of type %s.', gettype($callback))
        );
        // @codeCoverageIgnoreEnd
    }

    const DEFAULT_NULL = ' = null';
    const NS = '\\';
    const UNION = '|';
    const INTERSECTION = '&';
    const OPEN_PAREN = '(';
    const CLOSE_PAREN = ')';
}
