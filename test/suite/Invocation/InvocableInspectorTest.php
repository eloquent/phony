<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Test\TestInvocable;
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
    }

    public function testCallbackReflectorFailure()
    {
        $this->setExpectedException('ReflectionException');
        $this->subject->callbackReflector(111);
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
