<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class MockProxyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $this->mock = $this->mockBuilder->create();
        $property = new ReflectionProperty($this->mock, '_stubs');
        $property->setAccessible(true);
        $this->stubs = $property->getValue($this->mock);
        $this->subject = new MockProxy($this->mock, $this->stubs);

        $this->className = $this->mockBuilder->className();
    }

    public function testConstructor()
    {
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->stubs, $this->subject->stubs());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockProxy($this->mock);

        $this->assertSame($this->stubs, $this->subject->stubs());
    }

    public function testFull()
    {
        $this->assertSame($this->subject, $this->subject->full());
        $this->assertNull($this->mock->testClassAMethodA());
        $this->assertNull($this->mock->testClassAMethodB('a', 'b'));
    }

    public function testStubMethods()
    {
        $this->assertSame($this->stubs['testClassAMethodA'], $this->subject->stub('testClassAMethodA'));
        $this->assertSame($this->stubs['testClassAMethodA'], $this->subject->testClassAMethodA);
        $this->assertSame('ab', $this->mock->testClassAMethodA('a', 'b'));
        $this->assertSame($this->stubs['testClassAMethodA'], $this->subject->testClassAMethodA('a')->returns('x'));
        $this->assertSame('x', $this->mock->testClassAMethodA('a', 'b'));
    }

    public function testStubFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
        $this->subject->stub('nonexistent');
    }

    public function testMagicPropertyFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedPropertyException');
        $this->subject->nonexistent;
    }

    public function testMagicCallFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException');
        $this->subject->nonexistent();
    }
}
