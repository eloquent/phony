<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

/**
 * Normalizes the keys of arrays where the keys represent argument positions
 * and/or names.
 */
class ArgumentNormalizer
{
    /**
     * Normalize the supplied array using the supplied parameter names.
     *
     * References in the original array will be maintained in the normalized
     * array.
     *
     * @param array<int,string>       $parameterNames The parameter names.
     * @param array<int|string,mixed> $array          The array.
     *
     * @return array<int|string,mixed> The normalized array.
     */
    public function normalize(array $parameterNames, array $array)
    {
        $normalized = [];
        $positions = [];

        foreach ($array as $positionOrName => &$value) {
            if (is_string($positionOrName)) {
                $name = $positionOrName;
                $position = array_search($positionOrName, $parameterNames);

                if (false === $position) {
                    $position = null;
                }
            } else {
                $position = $positionOrName;
                $name = $parameterNames[$positionOrName] ?? null;
            }

            if (null === $name) {
                $key = $position;
            } else {
                $key = $name;
            }

            $normalized[$key] = $value;
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
