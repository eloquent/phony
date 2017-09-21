<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class InvalidDefinitionExceptionTest extends TestCase
{
    public function testException()
    {
        $name = 111;
        $value = 'value';
        $exception = new InvalidDefinitionException($name, $value);

        $this->assertSame($name, $exception->name());
        $this->assertSame($value, $exception->value());
        $this->assertSame('Invalid mock definition 111: (string).', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
