<?php

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class MultipleInheritanceExceptionTest extends TestCase
{
    public function testException()
    {
        $classNames = ['ClassNameA', 'ClassNameB', 'ClassNameC'];
        $exception = new MultipleInheritanceException($classNames);

        $this->assertSame($classNames, $exception->classNames());
        $this->assertSame(
            "Unable to extend 'ClassNameA' and 'ClassNameB' and 'ClassNameC' simultaneously.",
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
