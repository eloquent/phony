<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use ArrayIterator;
use IteratorAggregate;

class TestIteratorAggregate implements IteratorAggregate
{
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->values);
    }

    private $values;
}
