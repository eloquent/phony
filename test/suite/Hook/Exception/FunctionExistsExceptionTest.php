<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hook\Exception;

use PHPUnit\Framework\TestCase;

class FunctionExistsExceptionTest extends TestCase
{
    public function testException()
    {
        $functionName = 'functionName';
        $exception = new FunctionExistsException($functionName);

        $this->assertSame($functionName, $exception->functionName());
        $this->assertSame("Function 'functionName' is already defined.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
