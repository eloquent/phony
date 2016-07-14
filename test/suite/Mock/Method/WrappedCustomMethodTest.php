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

use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class WrappedCustomMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->customCallback = function () {
            return 'custom ' . implode(func_get_args());
        };
        $this->method = new ReflectionMethod($this, 'setUp');
        $this->mockBuilder = MockBuilderFactory::instance()->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->invoker = new Invoker();
        $this->subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);
    }

    public function testConstructor()
    {
        $this->assertSame($this->customCallback, $this->subject->customCallback());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('setUp', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, 'setUp'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodB');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);

        $this->assertSame($this->customCallback, $this->subject->customCallback());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodB', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertNull($this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestClassB', 'testClassAStaticMethodB'),
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
        $subject = $this->subject;

        $this->assertSame('custom ab', $subject('a', 'b'));
        $this->assertSame('custom ab', $subject->invoke('a', 'b'));
        $this->assertSame('custom ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('custom ', $subject->invokeWith());
    }

    public function testInvokeMethodsWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodB');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);

        $this->assertSame('custom ab', $subject('a', 'b'));
        $this->assertSame('custom ab', $subject->invoke('a', 'b'));
        $this->assertSame('custom ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('custom ', $subject->invokeWith());
    }

    public function testInvokeWithReferences()
    {
        $this->customCallback = function (&$a) {
            $a = 'a';
        };
        $subject = new WrappedCustomMethod($this->customCallback, $this->method, $this->handle, $this->invoker);
        $a = null;
        $subject->invokeWith(array(&$a));

        $this->assertSame('a', $a);
    }
}
