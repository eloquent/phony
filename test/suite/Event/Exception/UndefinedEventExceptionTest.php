<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event\Exception;

use Exception;
use PHPUnit\Framework\TestCase;

class UndefinedEventExceptionTest extends TestCase
{
    public function testException()
    {
        $index = 111;
        $cause = new Exception();
        $exception = new UndefinedEventException($index, $cause);

        $this->assertSame($index, $exception->index());
        $this->assertSame('No event defined for index 111.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
