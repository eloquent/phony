<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Verification;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class StaticVerificationProxyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->state = (object) array('stubs' => (object) array(), 'isFull' => true);
        $this->stubFactory = new StubFactory();
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->assertionRenderer = new AssertionRenderer();
        $this->assertionRecorder = new AssertionRecorder();
        $this->wildcardMatcher = new WildcardMatcher();

        $this->featureDetector = FeatureDetector::instance();
    }

    protected function setUpWith($className)
    {
        $this->mockBuilder = new MockBuilder($className);
        $this->class = $this->mockBuilder->build(true);
        $this->subject = new StaticVerificationProxy(
            $this->class,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->wildcardMatcher
        );

        $this->className = $this->class->getName();

        $proxyProperty = $this->class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $proxy = $proxyProperty->getValue(null);

        $stateProperty = new ReflectionProperty('Eloquent\Phony\Mock\Proxy\AbstractProxy', 'state');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($proxy, $this->state);
    }

    public function testConstructor()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');

        $this->assertSame($this->class, $this->subject->clazz());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->state->stubs, $this->subject->stubs());
        $this->assertSame($this->state->isFull, $this->subject->isFull());
        $this->assertSame($this->stubFactory, $this->subject->stubFactory());
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->wildcardMatcher, $this->subject->wildcardMatcher());
    }

    public function testConstructorDefaults()
    {
        $this->mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $this->class = $this->mockBuilder->build(true);
        $this->subject = new StaticVerificationProxy($this->class);

        $this->assertEquals((object) array(), $this->subject->stubs());
        $this->assertFalse($this->subject->isFull());
        $this->assertSame(StubFactory::instance(), $this->subject->stubFactory());
        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(WildcardMatcher::instance(), $this->subject->wildcardMatcher());
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
        $className = $this->className;
        $className::testClassAStaticMethodA();

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA());
    }

    public function testMagicCallFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException');
        $this->subject->nonexistent();
    }

    public function testVerificationWithParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $this->subject->partial();
        $className = $this->className;
        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->testClassAStaticMethodA();
    }

    public function testVerificationWithTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->setUpWith('Eloquent\Phony\Test\TestTraitA');
        $this->subject->partial();
        $className = $this->className;
        $a = 'a';
        $className::testClassAStaticMethodA($a, 'b');

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->testClassAStaticMethodA();
    }

    public function testVerificationWithMagicMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $this->subject->partial();
        $className = $this->className;
        $className::nonexistent('a', 'b');

        $this->assertSame($this->subject, $this->subject->nonexistent('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->nonexistent();
    }

    public function testVerificationWithNoParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');
        $this->subject->partial();
        $className = $this->className;
        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->testClassAStaticMethodA();
    }

    public function testVerificationWithCustomMethod()
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
        $this->subject = new StaticVerificationProxy($this->class);
        $proxyProperty = $this->class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $proxy = $proxyProperty->getValue(null);
        $stateProperty = new ReflectionProperty('Eloquent\Phony\Mock\Proxy\AbstractProxy', 'state');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($proxy, $this->subject->state());
        $className::methodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->methodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->methodA();
    }
}
