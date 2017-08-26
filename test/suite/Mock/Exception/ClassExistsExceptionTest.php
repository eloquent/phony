<?php

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class ClassExistsExceptionTest extends TestCase
{
    public function testException()
    {
        $className = 'ClassName';
        $exception = new ClassExistsException($className);

        $this->assertSame($className, $exception->className());
        $this->assertSame("Class 'ClassName' is already defined.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
