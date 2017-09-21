<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hook\Exception;

use PHPUnit\Framework\TestCase;

class FunctionSignatureMismatchExceptionTest extends TestCase
{
    public function testException()
    {
        $functionName = 'functionName';
        $exception = new FunctionSignatureMismatchException($functionName);

        $this->assertSame($functionName, $exception->functionName());
        $this->assertSame(
            "Function 'functionName' has a different signature to the supplied callback.",
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
