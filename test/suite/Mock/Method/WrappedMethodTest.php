<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Test\TestClassA;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class WrappedMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callParentMethod = new ReflectionMethod($this, 'setUp');
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodE');
        $this->mockBuilder = new MockBuilder();
        $this->mock = $this->mockBuilder->create();
        $this->subject = new WrappedMethod($this->callParentMethod, $this->method, $this->mock);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, 'testClassAMethodE'), $this->subject->callback());
        $this->assertNull($this->subject->id());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodE');
        $this->subject = new WrappedMethod($this->callParentMethod, $this->method);

        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertNull($this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array('Eloquent\Phony\Test\TestClassA', 'testClassAStaticMethodE'), $this->subject->callback());
        $this->assertNull($this->subject->id());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new WrappedMethod($this->callParentMethod, $this->method);

        $this->assertNull($this->subject->mock());
        $this->assertSame(array(null, $this->method->getName()), $this->subject->callback());
    }

    public function testInvokeMethods()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $class = $mockBuilder->build();
        $callParentMethod = $class->getMethod('_callParent');
        $callParentMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodC');
        $mock = $mockBuilder->get();
        $subject = new WrappedMethod($callParentMethod, $method, $mock);

        $this->assertSame('protected ab', $subject('a', 'b'));
        $this->assertSame('protected ab', $subject->invoke('a', 'b'));
        $this->assertSame('protected ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('protected ', $subject->invokeWith());
    }

    public function testInvokeMethodsWithStatic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $class = $mockBuilder->build();
        $callParentMethod = $class->getMethod('_callParentStatic');
        $callParentMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodC');
        $subject = new WrappedMethod($callParentMethod, $method);

        $this->assertSame('protected ab', $subject('a', 'b'));
        $this->assertSame('protected ab', $subject->invoke('a', 'b'));
        $this->assertSame('protected ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('protected ', $subject->invokeWith());
    }
}
