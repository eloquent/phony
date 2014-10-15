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

use Eloquent\Phony\Test\TestClassA;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class AccessibleMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodE');
        $this->instance = new TestClassA();
        $this->subject = new AccessibleMethod($this->method, $this->instance);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('ReflectionMethod', $this->subject->method());
        $this->assertSame(
            $this->method->getDeclaringClass()->getName(),
            $this->subject->method()->getDeclaringClass()->getName()
        );
        $this->assertSame($this->method->getName(), $this->subject->method()->getName());
        $this->assertSame($this->instance, $this->subject->instance());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new AccessibleMethod($this->method);

        $this->assertNull($this->subject->instance());
    }

    public function testInvokeMethodsNonStatic()
    {
        $subject = $this->subject;

        $this->assertSame('private ab', $subject('a', 'b'));
        $this->assertSame('private ab', $subject->invoke('a', 'b'));
        $this->assertSame('private ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('private ', $subject->invokeWith());
    }

    public function testInvokeMethodsStatic()
    {
        $subject =
            new AccessibleMethod(new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodE'));

        $this->assertSame('private ab', $subject('a', 'b'));
        $this->assertSame('private ab', $subject->invoke('a', 'b'));
        $this->assertSame('private ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('private ', $subject->invokeWith());
    }

    public function testInvokeWithReferenceParameters()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodD');
        $this->subject = new AccessibleMethod($this->method, $this->instance);
        $first = null;
        $second = null;
        $this->subject->invokeWith(array(&$first, &$second));

        $this->assertSame('first', $first);
        $this->assertSame('second', $second);
    }
}
