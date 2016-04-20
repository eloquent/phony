<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Test\TestInvocable;
use Eloquent\Phony\Test\TestWrappedInvocable;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class InvocableInspectorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new InvocableInspector();

        $this->callback = function () {};
        $this->invocable = new TestInvocable();
        $this->wrappedInvocable = new TestWrappedInvocable($this->callback, null);

        $this->featureDetector = FeatureDetector::instance();
    }

    public function testCallbackReflector()
    {
        $this->assertEquals(
            new ReflectionMethod(__METHOD__),
            $this->subject->callbackReflector(array($this, __FUNCTION__))
        );
        $this->assertEquals(
            new ReflectionMethod(__METHOD__),
            $this->subject->callbackReflector(array(__CLASS__, __FUNCTION__))
        );
        $this->assertEquals(new ReflectionMethod(__METHOD__), $this->subject->callbackReflector(__METHOD__));
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

    public function testCallbackReflectorFailure()
    {
        $this->setExpectedException('ReflectionException');
        $this->subject->callbackReflector(111);
    }

    public function testCallbackReflectorFailureObject()
    {
        $this->setExpectedException('ReflectionException', 'Invalid callback.');
        $this->subject->callbackReflector((object) array());
    }

    public function testCallbackThisValue()
    {
        $this->assertSame($this, $this->subject->callbackThisValue(array($this, 'a')));
        $this->assertSame($this->invocable, $this->subject->callbackThisValue($this->invocable));
        $this->assertNull($this->subject->callbackThisValue(array('a', 'b')));
        $this->assertNull($this->subject->callbackThisValue('a::b'));
        $this->assertNull($this->subject->callbackThisValue('a'));
        $this->assertNull($this->subject->callbackThisValue((object) array()));

        if ($this->subject->isBoundClosureSupported()) {
            $this->assertSame($this, $this->subject->callbackThisValue($this->callback));
        } else {
            $this->assertNull($this->subject->callbackThisValue($this->callback));
        }
    }

    public function testCallbackReturnType()
    {
        $this->assertNull($this->subject->callbackReturnType(function () {}));

        if ($this->featureDetector->isSupported('return.type')) {
            $type = $this->subject->callbackReturnType(eval('return function () : int {};'));

            $this->assertInstanceOf('ReflectionType', $type);
            $this->assertSame('int', strval($type));

            $type = $this->subject->callbackReturnType(eval('return function () : stdClass {};'));

            $this->assertInstanceOf('ReflectionType', $type);
            $this->assertSame('stdClass', strval($type));
        }
    }

    public function testIsBoundClosureSupported()
    {
        $reflectorReflector = new ReflectionClass('ReflectionFunction');
        $expected = $reflectorReflector->hasMethod('getClosureThis');

        $this->assertSame($expected, $this->subject->isBoundClosureSupported());
        $this->assertSame($expected, $this->subject->isBoundClosureSupported());
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
