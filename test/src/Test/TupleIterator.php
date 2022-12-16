<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Iterator;

class TupleIterator implements Iterator
{
    public function __construct(array $tuples)
    {
        $this->tuples = $tuples;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->tuples[key($this->tuples)][1];
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->tuples[key($this->tuples)][0];
    }

    public function next(): void
    {
        next($this->tuples);
    }

    public function rewind(): void
    {
        reset($this->tuples);
    }

    public function valid(): bool
    {
        return null !== key($this->tuples);
    }

    private $tuples;
}
