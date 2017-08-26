<?php

namespace Eloquent\Phony\Mock\Exception;

use Exception;
use PHPUnit\Framework\TestCase;

class NonMockClassExceptionTest extends TestCase
{
    public function testException()
    {
        $className = 'ClassName';
        $cause = new Exception();
        $exception = new NonMockClassException($className, $cause);

        $this->assertSame($className, $exception->className());
        $this->assertSame("The class 'ClassName' is not a mock class.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
