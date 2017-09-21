<?php

declare(strict_types=1);

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
