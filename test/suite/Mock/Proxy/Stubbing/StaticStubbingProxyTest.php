<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Stubbing;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;

class StaticStubbingProxyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $this->class = $this->mockBuilder->build();
        $this->className = $this->class->getName();
        $property = $this->class->getProperty('_staticStubs');
        $property->setAccessible(true);
        $this->stubs = $this->expectedStubs($property->getValue(null));
        $this->subject = new StaticStubbingProxy($this->className, $this->stubs);
    }

    public function testConstructor()
    {
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->stubs, $this->subject->stubs());
    }

    public function testFull()
    {
        $className = $this->className;

        $this->assertSame($this->subject, $this->subject->full());
        $this->assertNull($className::testClassAStaticMethodA());
        $this->assertNull($className::testClassAStaticMethodB('a', 'b'));
    }

    public function testStubMethods()
    {
        $className = $this->className;

        $this->assertSame($this->stubs['testClassAStaticMethodA'], $this->subject->stub('testClassAStaticMethodA'));
        $this->assertSame($this->stubs['testClassAStaticMethodA'], $this->subject->testClassAStaticMethodA);
        $this->assertSame('ab', $className::testClassAStaticMethodA('a', 'b'));
        $this->assertSame(
            $this->stubs['testClassAStaticMethodA']->callback(),
            $this->subject->testClassAStaticMethodA('a', '*')->returns('x')
        );
        $this->assertSame('x', $className::testClassAStaticMethodA('a', 'b'));
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

    protected function expectedStubs(array $stubs)
    {
        foreach ($stubs as $name => $stub) {
            $stubs[$name] = StubVerifierFactory::instance()->create($stub->callback(), $stub);
        }

        return $stubs;
    }
}
