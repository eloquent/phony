<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use EmptyIterator;
use Iterator;
use IteratorAggregate;

/**
 * An empty iterator aggregate, used as a default return value for callables
 * with return type hints of IteratorAggregate.
 *
 * @codeCoverageIgnore
 */
final class EmptyIteratorAggregate implements IteratorAggregate
{
    /**
     * Get the iterator.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new EmptyIterator();
    }
}
