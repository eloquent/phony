<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use RuntimeException;

class TestClassWithToStringException
{
    public function __toString(): string
    {
        throw new RuntimeException('You done goofed');
    }
}
