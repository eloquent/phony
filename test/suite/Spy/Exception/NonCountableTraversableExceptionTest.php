<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy\Exception;

use EmptyIterator;
use PHPUnit\Framework\TestCase;

class NonCountableTraversableExceptionTest extends TestCase
{
    public function testException()
    {
        $traversable = new EmptyIterator();
        $exception = new NonCountableTraversableException($traversable);

        $this->assertSame(
            "Unable to count a traversable object of type 'EmptyIterator', " .
                'since it does not implement Countable.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame($traversable, $exception->traversable());
    }
}
