<?php

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class InvalidMockExceptionTest extends TestCase
{
    public function testException()
    {
        $value = 111;
        $exception = new InvalidMockException($value);

        $this->assertSame($value, $exception->value());
        $this->assertSame("Value of type 'integer' is not a mock.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithObject()
    {
        $value = (object) [];
        $exception = new InvalidMockException($value);

        $this->assertSame($value, $exception->value());
        $this->assertSame("Object of type 'stdClass' is not a mock.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
