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

use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;

class StaticStubbingProxyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->state = (object) array('stubs' => (object) array(), 'isFull' => true);
        $this->stubFactory = new StubFactory();
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->wildcardMatcher = new WildcardMatcher();
    }

    protected function setUpWith($className)
    {
        $this->mockBuilder = new MockBuilder($className);
        $this->class = $this->mockBuilder->build(true);
        $this->subject = new StaticStubbingProxy(
            $this->class,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );

        $this->className = $this->class->getName();

        $proxyProperty = $this->class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $proxyProperty->setValue(null, $this->subject);
    }

    public function testConstructor()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');

        $this->assertSame($this->class, $this->subject->clazz());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->state->stubs, $this->subject->stubs());
        $this->assertSame($this->state->isFull, $this->subject->isFull());
        $this->assertSame($this->state, $this->subject->state());
        $this->assertTrue($this->subject->hasParent());
        $this->assertTrue($this->subject->isMagic());
        $this->assertSame($this->stubFactory, $this->subject->stubFactory());
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
        $this->assertSame($this->wildcardMatcher, $this->subject->wildcardMatcher());
    }

    public function testConstructorDefaults()
    {
        $this->mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $this->class = $this->mockBuilder->build(true);
        $this->subject = new StaticStubbingProxy($this->class);

        $this->assertEquals((object) array(), $this->subject->stubs());
        $this->assertFalse($this->subject->isFull());
        $this->assertSame(StubFactory::instance(), $this->subject->stubFactory());
        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
        $this->assertSame(WildcardMatcher::instance(), $this->subject->wildcardMatcher());
    }

    public function testConstructorWithNoParent()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');

        $this->assertFalse($this->subject->hasParent());
        $this->assertFalse($this->subject->isMagic());
    }

    public function testFull()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $className = $this->className;

        $this->assertSame($this->subject, $this->subject->full());
        $this->assertTrue($this->subject->isFull());
        $this->assertNull($className::testClassAStaticMethodA());
        $this->assertNull($className::testClassAStaticMethodB('a', 'b'));
    }

    public function testPartial()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $className = $this->className;

        $this->assertSame($this->subject, $this->subject->partial());
        $this->assertFalse($this->subject->isFull());
        $this->assertSame('', $className::testClassAStaticMethodA());
        $this->assertSame('ab', $className::testClassAStaticMethodB('a', 'b'));
    }

    public function testStub()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->stub('testClassAStaticMethodA');

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->stub('testClassAStaticMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testClassAStaticMethodA);
    }

    public function testStubWithMagic()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $actual = $this->subject->stub('nonexistent');

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->stub('nonexistent'));
        $this->assertSame($actual, $this->subject->state()->stubs->nonexistent);
    }

    public function testStubFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
        $this->subject->stub('nonexistent');
    }

    public function testMagicProperty()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->testClassAStaticMethodA;

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->testClassAStaticMethodA);
        $this->assertSame($actual, $this->subject->state()->stubs->testClassAStaticMethodA);
    }

    public function testMagicPropertyFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedPropertyException');
        $this->subject->nonexistent;
    }

    public function testSpy()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->spy('testClassAStaticMethodA');

        $this->assertInstanceOf('Eloquent\Phony\Spy\Spy', $actual);
        $this->assertSame($actual, $this->subject->spy('testClassAStaticMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testClassAStaticMethodA->spy());
    }

    public function testReset()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $this->subject->stub('testClassAStaticMethodA');
        $this->subject->stub('testClassAStaticMethodB');
        $this->subject->reset();

        $this->assertSame(array(), get_object_vars($this->subject->stubs()));
    }

    public function testMagicCall()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->testClassAStaticMethodA();

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->testClassAStaticMethodA);
        $this->assertSame($actual, $this->subject->state()->stubs->testClassAStaticMethodA);
    }

    public function testMagicCallFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException');
        $this->subject->nonexistent();
    }

    public function testStubbingWithParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $this->subject->partial();
        $className = $this->className;
        $this->subject->testClassAStaticMethodA('a', 'b')->returns('x');

        $this->assertSame('x', $className::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('cd', $className::testClassAStaticMethodA('c', 'd'));
    }

    public function testStubbingWithMagicMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $this->subject->partial();
        $className = $this->className;
        $this->subject->nonexistent('a', 'b')->returns('x');

        $this->assertSame('x', $className::nonexistent('a', 'b'));
        $this->assertSame('static magic nonexistent cd', $className::nonexistent('c', 'd'));
    }

    public function testStubbingWithNoParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');
        $this->subject->partial();
        $className = $this->className;
        $this->subject->testClassAStaticMethodA('a', 'b')->returns('x');

        $this->assertSame('x', $className::testClassAStaticMethodA('a', 'b'));
        $this->assertNull($className::testClassAStaticMethodA('c', 'd'));
    }

    public function testStubbingWithCustomMethod()
    {
        $this->mockBuilder = new MockBuilder(
            null,
            array(
                'static methodA' => function () {
                    return implode(func_get_args());
                }
            )
        );
        $this->class = $this->mockBuilder->build(true);
        $className = $this->class->getName();
        $this->subject = new StaticStubbingProxy($this->class);
        $proxyProperty = $this->class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $proxyProperty->setValue(null, $this->subject);
        $this->subject->methodA('a', 'b')->returns('x');

        $this->assertSame('x', $className::methodA('a', 'b'));
        $this->assertSame('cd', $className::methodA('c', 'd'));
    }
}
