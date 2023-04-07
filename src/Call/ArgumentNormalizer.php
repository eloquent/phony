<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

/**
 * Normalizes argument lists.
 */
class ArgumentNormalizer
{
    /**
     * Normalize the supplied arguments using the supplied parameter names.
     *
     * References in the original arguments will be maintained in the normalized
     * arguments.
     *
     * @param array<int,string>       $parameterNames The parameter names.
     * @param array<int|string,mixed> $arguments      The arguments.
     *
     * @return array<int|string,mixed> The normalized arguments.
     */
    public function normalize(array $parameterNames, array $arguments)
    {
        $normalized = [];
        $positions = [];

        foreach ($arguments as $positionOrName => &$value) {
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
