<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
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
    }

    public function testCallbackReflectorWithString()
    {
        $this->assertEquals(new ReflectionFunction('implode'), $this->subject->callbackReflector('implode'));
    }

    public function testCallbackReflectorWithStaticMethodArray()
    {
        $this->assertEquals(
            new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodA'),
            $this->subject->callbackReflector([TestClassA::class, 'testClassAStaticMethodA'])
        );
    }

    public function testCallbackReflectorWithInstanceMethodArray()
    {
        $this->assertEquals(new ReflectionMethod(__METHOD__), $this->subject->callbackReflector([$this, __FUNCTION__]));
    }

    public function testCallbackReflectorWithStaticMethodString()
    {
        $this->assertEquals(
            new ReflectionMethod(TestClassA::class, 'testClassAStaticMethodA'),
            $this->subject->callbackReflector(TestClassA::class . '::testClassAStaticMethodA')
        );
    }

    /**
     * @requires PHP < 8.2
     */
    public function testCallbackReflectorWithRelativeStaticMethodArray()
    {
        $this->assertEquals(
            new ReflectionMethod(TestClassA::class, 'testClassAStaticMethodA'),
            $this->subject->callbackReflector([TestClassB::class, 'parent::testClassAStaticMethodA'])
        );
    }

    public function testCallbackReflectorWithInvocable()
    {
        $invocable = new TestInvocable();

        $this->assertEquals(
            new ReflectionMethod($invocable, '__invoke'),
            $this->subject->callbackReflector($invocable)
        );
    }

    public function testCallbackReflectorWithClosure()
    {
        $closure = function () {};

        $this->assertEquals(new ReflectionFunction($closure), $this->subject->callbackReflector($closure));
    }

    public function testCallbackReflectorWithWrappedInvocable()
    {
        $callback = function () {};
        $wrappedInvocable = new TestWrappedInvocable($callback);

        $this->assertEquals(new ReflectionFunction($callback), $this->subject->callbackReflector($wrappedInvocable));
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
