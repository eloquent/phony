<?php

namespace Eloquent\Phony\Call\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedResponseExceptionTest extends TestCase
{
    public function testConstructor()
    {
        $message = 'You done goofed.';
        $exception = new UndefinedResponseException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
