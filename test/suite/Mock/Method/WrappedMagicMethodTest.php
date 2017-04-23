<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class WrappedMagicMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockBuilderFactory = MockBuilderFactory::instance();

        $this->name = 'nonexistent';
        $this->callMagicMethod = new ReflectionMethod($this, 'setUp');
        $this->isUncallable = false;
        $this->mockBuilder = $this->mockBuilderFactory->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->subject = new WrappedMagicMethod(
            $this->name,
            $this->callMagicMethod,
            $this->isUncallable,
            $this->handle,
            'return-value'
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->callMagicMethod, $this->subject->callMagicMethod());
        $this->assertSame($this->isUncallable, $this->subject->isUncallable());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, '__call'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->callMagicMethod = new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodB');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedMagicMethod(
            $this->name,
            $this->callMagicMethod,
            $this->isUncallable,
            $this->handle,
            'return-value'
        );

        $this->assertSame($this->callMagicMethod, $this->subject->callMagicMethod());
        $this->assertSame($this->isUncallable, $this->subject->isUncallable());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertNull($this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestClassB', '__callStatic'),
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
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $callMagicMethod = $class->getMethod('_callMagic');
        $callMagicMethod->setAccessible(true);
        $mock = $mockBuilder->get();
        $handle = $this->handleFactory->instanceHandle($mock);
        $subject = new WrappedMagicMethod($this->name, $callMagicMethod, false, $handle, 'return-value');

        $this->assertSame('magic nonexistent ab', $subject('a', 'b'));
        $this->assertSame('magic nonexistent ab', $subject->invoke('a', 'b'));
        $this->assertSame('magic nonexistent ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('magic nonexistent ', $subject->invokeWith());
    }

    public function testInvokeMethodsWithStatic()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $callMagicMethod = $class->getMethod('_callMagicStatic');
        $callMagicMethod->setAccessible(true);
        $handle = $this->handleFactory->staticHandle($mockBuilder->build());
        $subject = new WrappedMagicMethod($this->name, $callMagicMethod, false, $handle, 'return-value');

        $this->assertSame('static magic nonexistent ab', $subject('a', 'b'));
        $this->assertSame('static magic nonexistent ab', $subject->invoke('a', 'b'));
        $this->assertSame('static magic nonexistent ab', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('static magic nonexistent ', $subject->invokeWith());
    }

    public function testInvokeMethodsWithUncallable()
    {
        $subject = new WrappedMagicMethod($this->name, $this->callMagicMethod, true, $this->handle, 'return-value');

        $this->assertSame('return-value', $subject('a', 'b'));
        $this->assertSame('return-value', $subject->invoke('a', 'b'));
        $this->assertSame('return-value', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('return-value', $subject->invokeWith());
    }
}
