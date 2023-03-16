<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use AllowDynamicProperties;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassB;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[AllowDynamicProperties]
class WrappedCustomMethodTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new FacadeContainer();
        $this->mockBuilder = $container->mockBuilderFactory->create();
        $this->handleFactory = $container->handleFactory;

        $this->customCallback = function () {
            return 'custom ' . implode(func_get_args());
        };
        $this->method = new ReflectionMethod($this, 'setUp');
        $this->mock = $this->mockBuilder->partial();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->invoker = new Invoker();
        $this->subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);
    }

    public function testConstructor()
    {
        $this->assertSame($this->customCallback, $this->subject->customCallback());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertEquals($this->method->getParameters(), $this->subject->parameters());
        $this->assertSame('setUp', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertNull($this->subject->callback());
        $this->assertSame('', $this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodB');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);

        $this->assertSame($this->customCallback, $this->subject->customCallback());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodB', $this->subject->name());
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
        $subject = $this->subject;

        $this->assertSame('custom ab', $subject('a', 'b'));
        $this->assertSame('custom ab', $subject->invoke('a', 'b'));
        $this->assertSame('custom ab', $subject->invokeWith(['a', 'b']));
        $this->assertSame('custom ', $subject->invokeWith());
    }

    public function testInvokeMethodsWithStatic()
    {
        $this->method = new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodB');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);

        $this->assertSame('custom ab', $subject('a', 'b'));
        $this->assertSame('custom ab', $subject->invoke('a', 'b'));
        $this->assertSame('custom ab', $subject->invokeWith(['a', 'b']));
        $this->assertSame('custom ', $subject->invokeWith());
    }

    public function testInvokeWithReferences()
    {
        $this->customCallback = function (&$a) {
            $a = 'a';
        };
        $subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);
        $a = null;
        $subject->invokeWith([&$a]);

        $this->assertSame('a', $a);
    }
}
