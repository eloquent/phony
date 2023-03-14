<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedPositionalArgumentExceptionTest extends TestCase
{
    public function testException()
    {
        $index = 111;
        $exception = new UndefinedPositionalArgumentException($index);

        $this->assertSame($index, $exception->index());
        $this->assertFalse($exception->isNamed());
        $this->assertSame('No positional argument defined for index 111.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
