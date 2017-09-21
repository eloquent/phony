<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class InvalidMockClassExceptionTest extends TestCase
{
    public function testException()
    {
        $value = 111;
        $exception = new InvalidMockClassException($value);

        $this->assertSame($value, $exception->value());
        $this->assertSame("Value of type 'integer' is not a mock class.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
