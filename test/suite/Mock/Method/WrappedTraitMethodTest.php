<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use AllowDynamicProperties;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Test\TestTraitA;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[AllowDynamicProperties]
class WrappedTraitMethodTest extends TestCase
{
    protected function setUp(): void
    {
        $this->mockBuilderFactory = MockBuilderFactory::instance();

        $this->callTraitMethod = new ReflectionMethod($this, 'setUp');
        $this->traitName = TestTraitA::class;
        $this->method = new ReflectionMethod(TestTraitA::class . '::testClassAMethodB');
        $this->mockBuilder = $this->mockBuilderFactory->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->subject = new WrappedTraitMethod(
            $this->callTraitMethod,
            $this->method,
            $this->traitName,
            $this->handle
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->traitName, $this->subject->traitName());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAMethodB', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertNull($this->subject->callback());
        $this->assertSame('', $this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod(TestTraitA::class . '::testClassAStaticMethodA');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedTraitMethod(
            $this->callTraitMethod,
            $this->method,
            $this->traitName,
            $this->handle
        );

        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodA', $this->subject->name());
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
        $traitName = TestTraitA::class;
        $mockBuilder = $this->mockBuilderFactory->create($traitName);
        $class = $mockBuilder->build();
        $callTraitMethod = $class->getMethod('_callTrait');
        $callTraitMethod->setAccessible(true);
        $method = new ReflectionMethod(TestTraitA::class . '::testClassAMethodB');
        $mock = $mockBuilder->get();
        $handle = $this->handleFactory->instanceHandle($mock);
        $subject = new WrappedTraitMethod(
            $callTraitMethod,
            $method,
            $traitName,
            $handle
        );

        $this->assertSame('ab', $subject('a', 'b'));
        $this->assertSame('ab', $subject->invoke('a', 'b'));
        $this->assertSame('ab', $subject->invokeWith(['a', 'b']));
    }

    public function testInvokeMethodsWithStatic()
    {
        $traitName = TestTraitA::class;
        $mockBuilder = $this->mockBuilderFactory->create($traitName);
        $class = $mockBuilder->build();
        $callTraitMethod = $class->getMethod('_callTraitStatic');
        $callTraitMethod->setAccessible(true);
        $method = new ReflectionMethod(TestTraitA::class . '::testClassAStaticMethodA');
        $handle = $this->handleFactory->staticHandle($mockBuilder->build());
        $subject = new WrappedTraitMethod(
            $callTraitMethod,
            $method,
            $traitName,
            $handle
        );
        $a = 'a';

        $this->assertSame('ab', $subject->invokeWith([&$a, 'b']));
    }
}
