<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Collection;

use Eloquent\Phony\Collection\Exception\UndefinedIndexException;

/**
 * Normalizes collection indices.
 */
class IndexNormalizer implements IndexNormalizerInterface
{
    /**
     * Get the static instance of this normamlizer.
     *
     * @return IndexNormalizerInterface The static normamlizer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Normalize the supplied index.
     *
     * @param integer $size  The size of the collection.
     * @param integer $index The index.
     *
     * @return integer                 The normalized index.
     * @throws UndefinedIndexException If the index is invalid.
     */
    public function normalize($size, $index)
    {
        if ($size < 1) {
            throw new UndefinedIndexException($index);
        }

        if ($index < 0) {
            $potential = $size + $index;

            if ($potential < 0) {
                throw new UndefinedIndexException($index);
            }
        } else {
            $potential = $index;
        }

        if ($potential >= $size) {
            throw new UndefinedIndexException($index);
        }

        return $potential;
    }

    /**
     * Normalize the supplied index.
     *
     * @param integer $size        The size of the collection.
     * @param integer $index       The index.
     * @param mixed   &$normalized Set to the normalized index if successful.
     *
     * @return boolean True if the index can be normalized.
     */
    public function tryNormalize($size, $index, &$normalized = null)
    {
        $normalized = null;

        if ($size < 1) {
            return false;
        }

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

    private static $instance;
}
