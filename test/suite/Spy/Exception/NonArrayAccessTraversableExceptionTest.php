<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy\Exception;

use EmptyIterator;
use PHPUnit\Framework\TestCase;

class NonArrayAccessTraversableExceptionTest extends TestCase
{
    public function testException()
    {
        $traversable = new EmptyIterator();
        $exception = new NonArrayAccessTraversableException($traversable);

        $this->assertSame(
            "Unable to use array access on a traversable object of type 'EmptyIterator', " .
                'since it does not implement ArrayAccess.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame($traversable, $exception->traversable());
    }
}
