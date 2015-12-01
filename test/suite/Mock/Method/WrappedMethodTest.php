<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
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
        $this->proxyFactory = new ProxyFactory();
        $this->proxy = $this->proxyFactory->createStubbing($this->mock);
        $this->subject = new WrappedMethod($this->callParentMethod, $this->method, $this->proxy);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAMethodE', $this->subject->name());
        $this->assertSame($this->proxy, $this->subject->proxy());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, 'testClassAMethodE'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodE');
        $this->proxy = $this->proxyFactory->createStubbingStatic($this->mockBuilder->build());
        $this->subject = new WrappedMethod($this->callParentMethod, $this->method, $this->proxy);

        $this->assertSame($this->callParentMethod, $this->subject->callParentMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodE', $this->subject->name());
        $this->assertSame($this->proxy, $this->subject->proxy());
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
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $class = $mockBuilder->build();
        $callParentMethod = $class->getMethod('_callParent');
        $callParentMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodC');
        $mock = $mockBuilder->get();
        $proxy = $this->proxyFactory->createStubbing($mock);
        $subject = new WrappedMethod($callParentMethod, $method, $proxy);

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
        $proxy = $this->proxyFactory->createStubbingStatic($mockBuilder->build());
        $subject = new WrappedMethod($callParentMethod, $method, $proxy);

        $this->assertSame('protected ab', $subject('a', 'b'));
        $this->assertSame('protected ab', $subject->invoke('a', 'b'));
        $this->assertSame('protected ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('protected ', $subject->invokeWith());
    }
}
