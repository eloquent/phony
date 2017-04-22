<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class WrappedParentMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockBuilderFactory = MockBuilderFactory::instance();

        $this->callParentMethod = new ReflectionMethod($this, 'setUp');
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodE');
        $this->mockBuilder = $this->mockBuilderFactory->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->subject = new WrappedParentMethod($this->callParentMethod, $this->method, $this->handle);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAMethodE', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, 'testClassAMethodE'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodE');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedParentMethod($this->callParentMethod, $this->method, $this->handle);

        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodE', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertNull($this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestClassA', 'testClassAStaticMethodE'),
            $this->subject->callback()
        );
        $this->assertNull($this->subject->label());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(null));
        $this->assertNull($this->subject->label());

        $this->subject->setLabel('label');

        $this->assertSame('label', $this->subject->label());
    }

    public function testInvokeMethods()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassA');
        $class = $mockBuilder->build();
        $callParentMethod = $class->getMethod('_callParent');
        $callParentMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodC');
        $mock = $mockBuilder->get();
        $handle = $this->handleFactory->instanceHandle($mock);
        $subject = new WrappedParentMethod($callParentMethod, $method, $handle);

        $this->assertSame('protected ab', $subject('a', 'b'));
        $this->assertSame('protected ab', $subject->invoke('a', 'b'));
        $this->assertSame('protected ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('protected ', $subject->invokeWith());
    }

    public function testInvokeMethodsWithStatic()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassA');
        $class = $mockBuilder->build();
        $callParentMethod = $class->getMethod('_callParentStatic');
        $callParentMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodC');
        $handle = $this->handleFactory->staticHandle($mockBuilder->build());
        $subject = new WrappedParentMethod($callParentMethod, $method, $handle);

        $this->assertSame('protected ab', $subject('a', 'b'));
        $this->assertSame('protected ab', $subject->invoke('a', 'b'));
        $this->assertSame('protected ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('protected ', $subject->invokeWith());
    }
}
