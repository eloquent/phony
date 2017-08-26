<?php

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class AnonymousClassExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new AnonymousClassException();

        $this->assertSame('Anonymous classes cannot be mocked.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
