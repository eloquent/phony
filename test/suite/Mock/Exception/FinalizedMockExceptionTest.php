<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class FinalizedMockExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new FinalizedMockException();

        $this->assertSame('Unable to modify a finalized mock.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
