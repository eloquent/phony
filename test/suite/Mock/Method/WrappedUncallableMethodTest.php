<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\WithDynamicProperties;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

class WrappedUncallableMethodTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->method = new ReflectionMethod(TestClassA::class . '::testClassAMethodA');
        $this->mockBuilder = MockBuilderFactory::instance()->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->subject = new WrappedUncallableMethod($this->method, $this->handle, null, 'return-value');
    }

    public function testConstructor()
    {
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAMethodA', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertNull($this->subject->callback());
        $this->assertSame('', $this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodA');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedUncallableMethod($this->method, $this->handle, null, 'return-value');

        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodA', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertNull($this->subject->mock());
        $this->assertNull($this->subject->callback());
        $this->assertSame('', $this->subject->label());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(''));
        $this->assertSame('', $this->subject->label());

        $this->subject->setLabel('label');

        $this->assertSame('label', $this->subject->label());
    }

    public function testInvokeMethods()
    {
        $subject = $this->subject;

        $this->assertSame('return-value', $subject('a', 'b'));
        $this->assertSame('return-value', $subject->invoke('a', 'b'));
        $this->assertSame('return-value', $subject->invokeWith(['a', 'b']));
        $this->assertSame('return-value', $subject->invokeWith());
    }

    public function testSystemInvokeWithException()
    {
        $expected = new RuntimeException('You done goofed.');
        $subject = new WrappedUncallableMethod($this->method, $this->handle, $expected, null);

        $this->expectExceptionObject($expected);
        $this->assertSame('return-value', $subject());
    }

    public function testInvokeWithException()
    {
        $expected = new RuntimeException('You done goofed.');
        $subject = new WrappedUncallableMethod($this->method, $this->handle, $expected, null);

        $this->expectExceptionObject($expected);
        $this->assertSame('return-value', $subject->invoke());
    }

    public function testInvokeWithWithException()
    {
        $expected = new RuntimeException('You done goofed.');
        $subject = new WrappedUncallableMethod($this->method, $this->handle, $expected, null);

        $this->expectExceptionObject($expected);
        $this->assertSame('return-value', $subject->invokeWith());
    }
}
