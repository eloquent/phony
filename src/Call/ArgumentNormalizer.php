<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use InvalidArgumentException;

/**
 * Normalizes argument lists.
 */
class ArgumentNormalizer
{
    /**
     * Comparator for sorting variadic argument keys.
     */
    public static function compareVariadicKeys(
        int|string $a,
        int|string $b,
    ): int {
        $aIsPositional = is_int($a);
        $bIsPositional = is_int($b);

        if ($aIsPositional && !$bIsPositional) {
            return -1;
        }
        if (!$aIsPositional && $bIsPositional) {
            return 1;
        }

        return $a < $b ? -1 : 1;
    }

    /**
     * Normalize the supplied arguments using the supplied parameter names.
     *
     * References in the original arguments will be maintained in the normalized
     * arguments.
     *
     * @param array<int,string>       $parameterNames The parameter names.
     * @param array<int|string,mixed> $arguments      The arguments.
     *
     * @return array<int|string,mixed>  The normalized arguments.
     * @throws InvalidArgumentException If the supplied arguments are invalid.
     */
    public function normalize(array $parameterNames, array $arguments)
    {
        $keyMap = [];

        foreach ($parameterNames as $position => $name) {
            $keyMap[$position] = $name;
            $keyMap[$name] = $position;
        }

        $declaredCount = count($parameterNames);
        $declaredArguments = [];
        $variadicArguments = [];
        $seenArguments = [];
        $hasNamedArgument = false;
        $position = -1;

        foreach ($arguments as $key => &$value) {
            ++$position;

            if (is_int($key)) {
                if ($hasNamedArgument) {
                    throw new InvalidArgumentException(
                        'Cannot use a positional argument ' .
                        'after a named argument.'
                    );
                }

                if ($position < $declaredCount) {
                    $declaredArguments[$position] = &$value;
                    $seenArguments[$position] = true;
                } else {
                    $variadicArguments[$position] = &$value;
                    $seenArguments[$position] = true;
                }
            } else {
                $hasNamedArgument = true;

                if (isset($keyMap[$key])) {
                    $position = $keyMap[$key];

                    if (isset($seenArguments[$position])) {
                        throw new InvalidArgumentException(
                            "Named argument $$key overwrites previous argument."
                        );
                    }

                    $declaredArguments[$position] = &$value;
                    $seenArguments[$position] = true;
                } else {
                    $variadicArguments[$key] = &$value;
                    $seenArguments[$key] = true;
                }
            }
        }

        $normalized = [];

        foreach ($parameterNames as $position => $name) {
            if (array_key_exists($position, $declaredArguments)) {
                $normalized[$name] = &$declaredArguments[$position];
            }
        }

        uksort($variadicArguments, [__CLASS__, 'compareVariadicKeys']);

        foreach ($variadicArguments as $key => &$value) {
            $normalized[$key] = &$value;
        }

        return $normalized;
    }
}
