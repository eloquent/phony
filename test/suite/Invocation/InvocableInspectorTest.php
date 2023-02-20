<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestInvocable;
use Eloquent\Phony\Test\TestWrappedInvocable;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;

#[AllowDynamicProperties]
class InvocableInspectorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->invocableInspector;

        $this->callback = function () {};
        $this->invocable = new TestInvocable();
        $this->wrappedInvocable = new TestWrappedInvocable($this->callback);
    }

    public function testCallbackReflector()
    {
        $this->assertEquals(
            new ReflectionMethod(__METHOD__),
            $this->subject->callbackReflector([$this, __FUNCTION__])
        );
        $this->assertEquals(
            new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodA'),
            $this->subject->callbackReflector([TestClassA::class, 'testClassAStaticMethodA'])
        );
        $this->assertEquals(
            new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodA'),
            $this->subject->callbackReflector(TestClassA::class . '::testClassAStaticMethodA')
        );
        $this->assertEquals(new ReflectionFunction('implode'), $this->subject->callbackReflector('implode'));
        $this->assertEquals(
            new ReflectionFunction($this->callback),
            $this->subject->callbackReflector($this->callback)
        );
        $this->assertEquals(
            new ReflectionMethod($this->invocable, '__invoke'),
            $this->subject->callbackReflector($this->invocable)
        );
        $this->assertEquals(
            new ReflectionFunction($this->callback),
            $this->subject->callbackReflector($this->wrappedInvocable)
        );
    }

    public function testCallbackReflectorWithWrappedMethod()
    {
        $handle = $this->container->handleFactory->instanceHandle(
            $this->container->mockBuilderFactory->create(TestClassA::class)->full()
        );

        $this->assertEquals(
            new ReflectionMethod($handle->className() . '::testClassAMethodA'),
            $this->subject->callbackReflector($handle->testClassAMethodA)
        );
    }
}
