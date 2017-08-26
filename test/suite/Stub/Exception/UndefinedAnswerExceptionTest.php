<?php

namespace Eloquent\Phony\Stub\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedAnswerExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new UndefinedAnswerException();

        $this->assertSame('No answer was defined, or the answer is incomplete.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
