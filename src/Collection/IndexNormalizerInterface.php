<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Collection;

use Eloquent\Phony\Collection\Exception\UndefinedIndexException;

/**
 * The interface implemented by index normalizers.
 */
interface IndexNormalizerInterface
{
    /**
     * Normalize the supplied index.
     *
     * @param integer $size  The size of the collection.
     * @param integer $index The index.
     *
     * @return integer                 The normalized index.
     * @throws UndefinedIndexException If the index is invalid.
     */
    public function normalize($size, $index);

    /**
     * Normalize the supplied index.
     *
     * @param integer $size        The size of the collection.
     * @param integer $index       The index.
     * @param mixed   &$normalized Set to the normalized index if successful.
     *
     * @return boolean True if the index can be normalized.
     */
    public function tryNormalize($size, $index, &$normalized = null);
}
