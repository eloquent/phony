<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder\Method;

use Eloquent\Phony\Test\WithDynamicProperties;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

class CustomMethodDefinitionTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->isStatic = false;
        $this->name = 'name';
        $this->callback = function () {};
        $this->method = new ReflectionFunction($this->callback);
        $this->subject = new CustomMethodDefinition($this->isStatic, $this->name, $this->callback, $this->method);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->subject->isCallable());
        $this->assertSame($this->isStatic, $this->subject->isStatic());
        $this->assertTrue($this->subject->isCustom());
        $this->assertSame('public', $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->method, $this->subject->method());
    }
}
