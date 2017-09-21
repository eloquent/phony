<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestInvocable;
use Eloquent\Phony\Test\TestWrappedInvocable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionType;

class InvocableInspectorTest extends TestCase
{
    protected function setUp()
    {
        $this->subject = new InvocableInspector();

        $this->callback = function () {};
        $this->invocable = new TestInvocable();
        $this->wrappedInvocable = new TestWrappedInvocable($this->callback);

        $this->featureDetector = FeatureDetector::instance();
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

    public function testCallbackReturnType()
    {
        $this->assertNull($this->subject->callbackReturnType(function () {}));

        $type = $this->subject->callbackReturnType(eval('return function () : int {};'));

        $this->assertInstanceOf(ReflectionType::class, $type);
        $this->assertSame('int', strval($type));

        $type = $this->subject->callbackReturnType(eval('return function () : stdClass {};'));

        $this->assertInstanceOf(ReflectionType::class, $type);
        $this->assertSame('stdClass', strval($type));
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
