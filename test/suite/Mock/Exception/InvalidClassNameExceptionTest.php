<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class InvalidClassNameExceptionTest extends TestCase
{
    public function testException()
    {
        $className = '1';
        $exception = new InvalidClassNameException($className);

        $this->assertSame($className, $exception->className());
        $this->assertSame("Invalid class name '1'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
