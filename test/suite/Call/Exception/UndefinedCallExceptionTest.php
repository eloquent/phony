<?php

namespace Eloquent\Phony\Call\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedCallExceptionTest extends TestCase
{
    public function testConstructor()
    {
        $index = 111;
        $exception = new UndefinedCallException($index);

        $this->assertSame($index, $exception->index());
        $this->assertSame('No call defined for index 111.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
