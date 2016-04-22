<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Phpunit;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Integration\Phpunit\PhpunitAssertionRecorder;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactory;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Test\TestEvent;
use PHPUnit_Framework_TestCase;
use ReflectionObject;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->assertionRecorder = PhpunitAssertionRecorder::instance();
        $this->callVerifierFactory = new CallVerifierFactory(
            MatcherFactory::instance(),
            MatcherVerifier::instance(),
            $this->assertionRecorder,
            AssertionRenderer::instance(),
            InvocableInspector::instance()
        );
        $this->stubVerifierFactory = new StubVerifierFactory(
            StubFactory::instance(),
            SpyFactory::instance(),
            MatcherFactory::instance(),
            MatcherVerifier::instance(),
            $this->callVerifierFactory,
            $this->assertionRecorder,
            AssertionRenderer::instance(),
            InvocableInspector::instance(),
            Invoker::instance(),
            GeneratorAnswerBuilderFactory::instance()
        );
        $this->handleFactory = new HandleFactory(
            StubFactory::instance(),
            $this->stubVerifierFactory,
            AssertionRenderer::instance(),
            $this->assertionRecorder,
            Invoker::instance()
        );

        $this->eventA = new TestEvent(0, 0.0);
        $this->eventB = new TestEvent(1, 1.0);
    }

    public function testMockBuilder()
    {
        $actual = Phony::mockBuilder('Eloquent\Phony\Test\TestClassA');

        $this->assertInstanceOf('Eloquent\Phony\Mock\Builder\MockBuilder', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->get());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $actual->get());
    }

    public function testMockBuilderFunction()
    {
        $actual = mockBuilder('Eloquent\Phony\Test\TestClassA');

        $this->assertInstanceOf('Eloquent\Phony\Mock\Builder\MockBuilder', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->get());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $actual->get());
    }

    public function testPartialMock()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = new Arguments(array('a', 'b'));
        $actual = Phony::partialMock($types, $arguments);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertSame(array('a', 'b'), $actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockWithNullArguments()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = null;
        $actual = Phony::partialMock($types, $arguments);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertNull($actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockWithNoArguments()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $actual = Phony::partialMock($types);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertEquals(array(), $actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockDefaults()
    {
        $actual = Phony::partialMock();

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
    }

    public function testPartialMockFunction()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = new Arguments(array('a', 'b'));
        $actual = partialMock($types, $arguments);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertSame(array('a', 'b'), $actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockFunctionWithNullArguments()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = null;
        $actual = partialMock($types, $arguments);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertNull($actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockFunctionWithNoArguments()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $actual = partialMock($types);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertEquals(array(), $actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockFunctionDefaults()
    {
        $actual = partialMock();

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
    }

    public function testMock()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $actual = Phony::mock($types);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertNull($actual->mock()->constructorArguments);
        $this->assertNull($actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testMockFunction()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $actual = mock($types);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertNull($actual->mock()->constructorArguments);
        $this->assertNull($actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testOnStatic()
    {
        $class = Phony::mockBuilder()->build();
        $actual = Phony::onStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StaticStubbingHandle', $actual);
        $this->assertSame($class, $actual->clazz());
    }

    public function testOnStaticFunction()
    {
        $class = mockBuilder()->build();
        $actual = onStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StaticStubbingHandle', $actual);
        $this->assertSame($class, $actual->clazz());
    }

    public function testOn()
    {
        $mock = Phony::mockBuilder()->partial();
        $actual = Phony::on($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertSame($mock, $actual->mock());
    }

    public function testOnFunction()
    {
        $mock = mockBuilder()->partial();
        $actual = on($mock);
        $expected = $this->handleFactory->createStubbing($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertSame($mock, $actual->mock());
    }

    public function testVerifyStatic()
    {
        $class = Phony::mockBuilder()->build();
        $actual = Phony::verifyStatic($class);
        $expected = $this->handleFactory->createVerificationStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandle', $actual);
        $this->assertSame($class, $actual->clazz());
    }

    public function testVerifyStaticFunction()
    {
        $class = mockBuilder()->build();
        $actual = verifyStatic($class);
        $expected = $this->handleFactory->createVerificationStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandle', $actual);
        $this->assertSame($class, $actual->clazz());
    }

    public function testVerify()
    {
        $mock = Phony::mockBuilder()->partial();
        $actual = Phony::verify($mock);
        $expected = $this->handleFactory->createVerification($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\InstanceVerificationHandle', $actual);
        $this->assertSame($mock, $actual->mock());
    }

    public function testVerifyFunction()
    {
        $mock = mockBuilder()->partial();
        $actual = verify($mock);
        $expected = $this->handleFactory->createVerification($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\InstanceVerificationHandle', $actual);
        $this->assertSame($mock, $actual->mock());
    }

    public function testSpy()
    {
        $callback = function () {};
        $actual = Phony::spy($callback);

        $this->assertInstanceOf('Eloquent\Phony\Spy\SpyVerifier', $actual);
        $this->assertSame($callback, $actual->callback());
        $this->assertSpyAssertionRecorder($this->assertionRecorder, $actual);
    }

    public function testSpyFunction()
    {
        $callback = function () {};
        $actual = spy($callback);

        $this->assertInstanceOf('Eloquent\Phony\Spy\SpyVerifier', $actual);
        $this->assertSame($callback, $actual->callback());
        $this->assertSpyAssertionRecorder($this->assertionRecorder, $actual);
    }

    public function testStub()
    {
        $callback = function () { return 'a'; };
        $actual = Phony::stub($callback);

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame('a', call_user_func($actual->stub()->callback()));
        $this->assertSame($actual->stub(), $actual->spy()->callback());
        $this->assertStubAssertionRecorder($this->assertionRecorder, $actual);
    }

    public function testStubFunction()
    {
        $callback = function () { return 'a'; };
        $actual = stub($callback);

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame('a', call_user_func($actual->stub()->callback()));
        $this->assertSame($actual->stub(), $actual->spy()->callback());
        $this->assertStubAssertionRecorder($this->assertionRecorder, $actual);
    }

    public function testEventOrderMethods()
    {
        $this->assertTrue((boolean) Phony::checkInOrder($this->eventA, $this->eventB));
        $this->assertFalse((boolean) Phony::checkInOrder($this->eventB, $this->eventA));
        $this->assertEquals(
            new EventSequence(array($this->eventA, $this->eventB)),
            Phony::inOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) Phony::checkInOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) Phony::checkInOrderSequence(array($this->eventB, $this->eventA)));
        $this->assertEquals(
            new EventSequence(array($this->eventA, $this->eventB)),
            Phony::inOrderSequence(array($this->eventA, $this->eventB))
        );
    }

    public function testInOrderMethodFailure()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
        Phony::inOrder($this->eventB, $this->eventA);
    }

    public function testInOrderSequenceMethodFailure()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
        Phony::inOrderSequence(array($this->eventB, $this->eventA));
    }

    public function testEventOrderFunctions()
    {
        $this->assertTrue((boolean) checkInOrder($this->eventA, $this->eventB));
        $this->assertFalse((boolean) checkInOrder($this->eventB, $this->eventA));
        $this->assertEquals(
            new EventSequence(array($this->eventA, $this->eventB)),
            inOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) checkInOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) checkInOrderSequence(array($this->eventB, $this->eventA)));
        $this->assertEquals(
            new EventSequence(array($this->eventA, $this->eventB)),
            inOrderSequence(array($this->eventA, $this->eventB))
        );
        $this->assertTrue((boolean) checkAnyOrder($this->eventA, $this->eventB));
        $this->assertFalse((boolean) checkAnyOrder());
        $this->assertEquals(
            new EventSequence(array($this->eventA, $this->eventB)),
            anyOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) checkAnyOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) checkAnyOrderSequence(array()));
        $this->assertEquals(
            new EventSequence(array($this->eventA, $this->eventB)),
            anyOrderSequence(array($this->eventA, $this->eventB))
        );
    }

    public function testInOrderFunctionFailure()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
        inOrder($this->eventB, $this->eventA);
    }

    public function testInOrderSequenceFunctionFailure()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
        inOrderSequence(array($this->eventB, $this->eventA));
    }

    public function testAny()
    {
        $expected = new AnyMatcher();
        $actual = Phony::any();

        $this->assertEquals($expected, $actual);
    }

    public function testAnyFunction()
    {
        $expected = new AnyMatcher();
        $actual = any();

        $this->assertEquals($expected, $actual);
    }

    public function testEqualTo()
    {
        $expected = new EqualToMatcher('a');
        $actual = Phony::equalTo('a');

        $this->assertEquals($expected, $actual);
    }

    public function testEqualToFunction()
    {
        $expected = new EqualToMatcher('a');
        $actual = equalTo('a');

        $this->assertEquals($expected, $actual);
    }

    public function testWildcard()
    {
        $expected = new WildcardMatcher(new EqualToMatcher('a'), 1, 2);
        $actual = Phony::wildcard('a', 1, 2);

        $this->assertEquals($expected, $actual);
    }

    public function testWildcardFunction()
    {
        $expected = new WildcardMatcher(new EqualToMatcher('a'), 1, 2);
        $actual = wildcard('a', 1, 2);

        $this->assertEquals($expected, $actual);
    }

    public function testSetExportDepth()
    {
        $this->assertSame(1, Phony::setExportDepth(111));
        $this->assertSame(111, Phony::setExportDepth(1));
    }

    public function testSetExportDepthFunction()
    {
        $this->assertSame(1, setExportDepth(111));
        $this->assertSame(111, setExportDepth(1));
    }

    private function assertSpyAssertionRecorder($expected, $spy)
    {
        $reflector = new ReflectionObject($spy);
        $property = $reflector->getProperty('callVerifierFactory');
        $property->setAccessible(true);

        $callVerifierFactory = $property->getValue($spy);

        $reflector = new ReflectionObject($callVerifierFactory);
        $property = $reflector->getProperty('assertionRecorder');
        $property->setAccessible(true);

        $assertionRecorder = $property->getValue($callVerifierFactory);

        $this->assertEquals($expected, $assertionRecorder);
    }

    private function assertStubAssertionRecorder($expected, $stub)
    {
        $reflector = new ReflectionObject($stub);
        $property = $reflector->getParentClass()->getProperty('callVerifierFactory');
        $property->setAccessible(true);

        $callVerifierFactory = $property->getValue($stub);

        $reflector = new ReflectionObject($callVerifierFactory);
        $property = $reflector->getProperty('assertionRecorder');
        $property->setAccessible(true);

        $assertionRecorder = $property->getValue($callVerifierFactory);

        $this->assertEquals($expected, $assertionRecorder);
    }
}
