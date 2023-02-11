<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use IteratorAggregate;
use Traversable;

class TestIteratorAggregate implements IteratorAggregate
{
    public function __construct($iterator)
    {
        $this->iterator = $iterator;
    }

    public function getIterator(): Traversable
    {
        return $this->iterator;
    }

    private $iterator;
}
