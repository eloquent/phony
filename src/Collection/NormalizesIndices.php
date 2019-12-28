<?php

declare(strict_types=1);

namespace Eloquent\Phony\Collection;

/**
 * Provides the normalizeIndex method.
 */
trait NormalizesIndices
{
    private function normalizeIndex(
        int $size,
        int $index,
        int &$normalized = null
    ): bool {
        $normalized = null;

        if ($index < 0) {
            $potential = $size + $index;

            if ($potential < 0) {
                return false;
            }
        } else {
            $potential = $index;
        }

        if ($potential >= $size) {
            return false;
        }

        $normalized = $potential;

        return true;
    }
}
