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

        $normalized = [];
        $positions = [];
        $hasNamedArgument = false;
        $seenPositions = [];
        $truePosition = -1;

        foreach ($arguments as $positionOrName => &$value) {
            ++$truePosition;

            if (is_string($positionOrName)) {
                $hasNamedArgument = true;

                $name = $positionOrName;
                $position = $keyMap[$name] ?? null;

                if (null !== $position && isset($seenPositions[$position])) {
                    throw new InvalidArgumentException(
                        "Named argument $$name overwrites previous argument."
                    );
                }
            } else {
                if ($hasNamedArgument) {
                    throw new InvalidArgumentException(
                        'Cannot use a positional argument ' .
                        'after a named argument.'
                    );
                }

                $position = $truePosition;
                $name = $parameterNames[$truePosition] ?? null;
            }

            if (null !== $position) {
                $seenPositions[$position] = true;
            }

            if (null === $name) {
                $key = $position;
            } else {
                $key = $name;
            }

            $normalized[$key] = &$value;
            $positions[$key] = $position;
        }

        uksort(
            $normalized,
            function (
                string|int $a,
                string|int $b
            ) use ($positions) {
                $aPosition = $positions[$a] ?? null;
                $bPosition = $positions[$b] ?? null;
                $aIsExplicit = null !== $aPosition;
                $bIsExplicit = null !== $bPosition;

                if ($aIsExplicit && $bIsExplicit) {
                    if ($aPosition < $bPosition) {
                        return -1;
                    }
                    if ($aPosition > $bPosition) {
                        return 1;
                    }
                }

                if ($aIsExplicit && !$bIsExplicit) {
                    return -1;
                }
                if (!$aIsExplicit && $bIsExplicit) {
                    return 1;
                }

                return $a < $b ? -1 : 1;
            }
        );

        return $normalized;
    }
}
