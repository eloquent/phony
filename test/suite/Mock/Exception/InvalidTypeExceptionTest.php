<?php

namespace Eloquent\Phony\Mock\Exception;

use Exception;
use PHPUnit\Framework\TestCase;

class InvalidTypeExceptionTest extends TestCase
{
    public function testException()
    {
        $type = null;
        $cause = new Exception();
        $exception = new InvalidTypeException($type, $cause);

        $this->assertSame($type, $exception->type());
        $this->assertSame("Unable to add type of type 'NULL'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }

    public function testExceptionWithString()
    {
        $type = 'Nonexistent';
        $cause = new Exception();
        $exception = new InvalidTypeException($type, $cause);

        $this->assertSame($type, $exception->type());
        $this->assertSame("Undefined type 'Nonexistent'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
