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

    public function current()
    {
        return $this->tuples[key($this->tuples)][1];
    }

    public function key()
    {
        return $this->tuples[key($this->tuples)][0];
    }

    public function next()
    {
        next($this->tuples);
    }

    public function rewind()
    {
        reset($this->tuples);
    }

    public function valid()
    {
        return null !== key($this->tuples);
    }

    private $tuples;
}
