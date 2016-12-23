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

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class WrappedUncallableMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodA');
        $this->mockBuilder = MockBuilderFactory::instance()->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->instanceHandle($this->mock);
        $this->subject = new WrappedUncallableMethod($this->method, $this->handle, 'return-value');
    }

    public function testConstructor()
    {
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAMethodA', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertSame(array($this->mock, 'testClassAMethodA'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodA');
        $this->handle = $this->handleFactory->staticHandle($this->mockBuilder->build());
        $this->subject = new WrappedUncallableMethod($this->method, $this->handle, 'return-value');

        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodA', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertNull($this->subject->mock());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestClassA', 'testClassAStaticMethodA'),
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

        $this->assertSame('return-value', $subject('a', 'b'));
        $this->assertSame('return-value', $subject->invoke('a', 'b'));
        $this->assertSame('return-value', $subject->invokeWith(array('a', 'b')));
        $this->assertSame('return-value', $subject->invokeWith());
    }
}
