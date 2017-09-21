<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class FinalClassExceptionTest extends TestCase
{
    public function testException()
    {
        $className = 'ClassName';
        $exception = new FinalClassException($className);

        $this->assertSame($className, $exception->className());
        $this->assertSame("Unable to extend final class 'ClassName'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
