<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedPositionalArgumentExceptionTest extends TestCase
{
    public function testException()
    {
        $position = 111;
        $exception = new UndefinedPositionalArgumentException($position);

        $this->assertSame($position, $exception->position());
        $this->assertFalse($exception->isNamed());
        $this->assertSame('No positional argument defined for position 111.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
