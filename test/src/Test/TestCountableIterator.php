<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Countable;
use Iterator;

class TestCountableIterator implements Countable, Iterator
{
    public function count(): int
    {
        return 0;
    }

    public function current(): int
    {
        return 0;
    }

    public function key(): int
    {
        return 0;
    }

    public function next(): void
    {
    }

    public function rewind(): void
    {
    }

    public function valid(): bool
    {
        return false;
    }
}
