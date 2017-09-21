<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedMethodStubExceptionTest extends TestCase
{
    public function testException()
    {
        $className = 'ClassName';
        $name = 'method';
        $exception = new UndefinedMethodStubException($className, $name);

        $this->assertSame($className, $exception->className());
        $this->assertSame($name, $exception->name());
        $this->assertSame('The requested method stub ClassName::method() does not exist.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
