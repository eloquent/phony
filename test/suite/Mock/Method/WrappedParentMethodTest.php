<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use AllowDynamicProperties;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Test\TestClassA;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[AllowDynamicProperties]
class WrappedParentMethodTest extends TestCase
{
    protected function setUp(): void
    {
        $this->mockBuilderFactory = MockBuilderFactory::instance();

        $this->callParentMethod = new ReflectionMethod($this, 'setUp');
        $this->method = new ReflectionMethod(TestClassA::class . '::testClassAMethodE');
        $this->mockBuilder = $this->mockBuilderFactory->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->subject = new WrappedParentMethod($this->callParentMethod, $this->method, $this->handle);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAMethodE', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertNull($this->subject->callback());
        $this->assertSame('', $this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodE');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedParentMethod($this->callParentMethod, $this->method, $this->handle);

        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodE', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertNull($this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
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
        $mockBuilder = $this->mockBuilderFactory->create(TestClassA::class);
        $class = $mockBuilder->build();
        $callParentMethod = $class->getMethod('_callParent');
        $callParentMethod->setAccessible(true);
        $method = new ReflectionMethod(TestClassA::class . '::testClassAMethodC');
        $mock = $mockBuilder->get();
        $handle = $this->handleFactory->instanceHandle($mock);
        $subject = new WrappedParentMethod($callParentMethod, $method, $handle);

        $this->assertSame('protected ab', $subject('a', 'b'));
        $this->assertSame('protected ab', $subject->invoke('a', 'b'));
        $this->assertSame('protected ab', $subject->invokeWith(['a', 'b']));
        $this->assertSame('protected ', $subject->invokeWith());
    }

    public function testInvokeMethodsWithStatic()
    {
        $mockBuilder = $this->mockBuilderFactory->create(TestClassA::class);
        $class = $mockBuilder->build();
        $callParentMethod = $class->getMethod('_callParentStatic');
        $callParentMethod->setAccessible(true);
        $method = new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodC');
        $handle = $this->handleFactory->staticHandle($mockBuilder->build());
        $subject = new WrappedParentMethod($callParentMethod, $method, $handle);

        $this->assertSame('protected ab', $subject('a', 'b'));
        $this->assertSame('protected ab', $subject->invoke('a', 'b'));
        $this->assertSame('protected ab', $subject->invokeWith(['a', 'b']));
        $this->assertSame('protected ', $subject->invokeWith());
    }
}
