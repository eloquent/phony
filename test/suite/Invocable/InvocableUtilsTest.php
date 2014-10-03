<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocable;

use Eloquent\Phony\Test\TestInvocable;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

class InvocableUtilsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $reflector = new ReflectionClass('Eloquent\Phony\Invocable\InvocableUtils');
        foreach ($reflector->getProperties(ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true);
            $property->setValue(null, null);
        }

        $this->callback = function () {};
        $this->invocable = new TestInvocable();
    }

    public function testCallWith()
    {
        $this->assertSame(phpversion(), InvocableUtils::callWith('phpversion'));
        $this->assertSame(1, InvocableUtils::callWith('strlen', array('a')));
        $this->assertSame(
            array('invokeWith', array('a', 'b')),
            InvocableUtils::callWith($this->invocable, array('a', 'b'))
        );
    }

    public function testCallbackReflector()
    {
        $this->assertEquals(
            new ReflectionMethod(__METHOD__),
            InvocableUtils::callbackReflector(array($this, __FUNCTION__))
        );
        $this->assertEquals(
            new ReflectionMethod(__METHOD__),
            InvocableUtils::callbackReflector(array(__CLASS__, __FUNCTION__))
        );
        $this->assertEquals(new ReflectionMethod(__METHOD__), InvocableUtils::callbackReflector(__METHOD__));
        $this->assertEquals(new ReflectionFunction('implode'), InvocableUtils::callbackReflector('implode'));
        $this->assertEquals(
            new ReflectionFunction($this->callback),
            InvocableUtils::callbackReflector($this->callback)
        );
    }

    public function testCallbackReflectorFailure()
    {
        $this->setExpectedException('ReflectionException');
        InvocableUtils::callbackReflector(111);
    }

    public function testCallbackThisValue()
    {
        $this->assertSame($this, InvocableUtils::callbackThisValue(array($this, 'a')));
        $this->assertSame($this->invocable, InvocableUtils::callbackThisValue($this->invocable));
        $this->assertNull(InvocableUtils::callbackThisValue(array('a', 'b')));
        $this->assertNull(InvocableUtils::callbackThisValue('a::b'));
        $this->assertNull(InvocableUtils::callbackThisValue('a'));
        $this->assertNull(InvocableUtils::callbackThisValue((object) array()));

        if (InvocableUtils::isBoundClosureSupported()) {
            $this->assertSame($this, InvocableUtils::callbackThisValue($this->callback));
        } else {
            $this->assertNull(InvocableUtils::callbackThisValue($this->callback));
        }
    }

    public function testIsBoundClosureSupported()
    {
        $reflectorReflector = new ReflectionClass('ReflectionFunction');
        $expected = $reflectorReflector->hasMethod('getClosureThis');

        $this->assertSame($expected, InvocableUtils::isBoundClosureSupported());
        $this->assertSame($expected, InvocableUtils::isBoundClosureSupported());
    }
}
