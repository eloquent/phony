<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedNamedArgumentExceptionTest extends TestCase
{
    public function testException()
    {
        $name = 'a';
        $exception = new UndefinedNamedArgumentException($name);

        $this->assertSame($name, $exception->name());
        $this->assertTrue($exception->isNamed());
        $this->assertSame("No named argument defined for name 'a'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
