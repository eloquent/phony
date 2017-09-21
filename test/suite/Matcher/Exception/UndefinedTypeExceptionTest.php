<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedTypeExceptionTest extends TestCase
{
    public function testException()
    {
        $type = Undefined::class;
        $exception = new UndefinedTypeException($type);

        $this->assertSame($type, $exception->type());
        $this->assertSame(
            "Undefined type 'Eloquent\\\\Phony\\\\Matcher\\\\Exception\\\\Undefined'.",
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
