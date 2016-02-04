<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactory;
use Eloquent\Phony\Test\TestEvent;
use PHPUnit_Framework_TestCase;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->handleFactory = new HandleFactory();

        $this->eventA = new TestEvent(0, 0.0);
        $this->eventB = new TestEvent(1, 1.0);
    }

    public function testMockBuilder()
    {
        $actual = Phony::mockBuilder('Eloquent\Phony\Test\TestClassA');

        $this->assertInstanceOf('Eloquent\Phony\Mock\Builder\MockBuilder', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->get());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $actual->get());
    }

    public function testMockBuilderFunction()
    {
        $actual = mockBuilder('Eloquent\Phony\Test\TestClassA');

        $this->assertInstanceOf('Eloquent\Phony\Mock\Builder\MockBuilder', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->get());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $actual->get());
    }

    public function testPartialMock()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = new Arguments(array('a', 'b'));
        $actual = Phony::partialMock($types, $arguments);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
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
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
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
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertEquals(array(), $actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockDefaults()
    {
        $actual = Phony::partialMock();

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
    }

    public function testPartialMockFunction()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = new Arguments(array('a', 'b'));
        $actual = partialMock($types, $arguments);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
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
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
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
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual->mock());
        $this->assertInstanceOf('Countable', $actual->mock());
        $this->assertEquals(array(), $actual->mock()->constructorArguments);
        $this->assertSame('ab', $actual->mock()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockFunctionDefaults()
    {
        $actual = partialMock();

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
    }

    public function testMock()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $actual = Phony::mock($types);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
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
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->mock());
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

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\VerificationHandle', $actual);
        $this->assertSame($mock, $actual->mock());
    }

    public function testVerifyFunction()
    {
        $mock = mockBuilder()->partial();
        $actual = verify($mock);
        $expected = $this->handleFactory->createVerification($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\VerificationHandle', $actual);
        $this->assertSame($mock, $actual->mock());
    }

    public function testSpy()
    {
        $callback = function () {};
        $actual = Phony::spy($callback);

        $this->assertInstanceOf('Eloquent\Phony\Spy\SpyVerifier', $actual);
        $this->assertSame($callback, $actual->callback());
    }

    public function testSpyFunction()
    {
        $callback = function () {};
        $actual = spy($callback);

        $this->assertInstanceOf('Eloquent\Phony\Spy\SpyVerifier', $actual);
        $this->assertSame($callback, $actual->callback());
    }

    public function testStub()
    {
        $callback = function () { return 'a'; };
        $actual = Phony::stub($callback);

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame('a', call_user_func($actual->stub()->callback()));
        $this->assertSame($actual->stub(), $actual->spy()->callback());
    }

    public function testStubFunction()
    {
        $callback = function () { return 'a'; };
        $actual = stub($callback);

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame('a', call_user_func($actual->stub()->callback()));
        $this->assertSame($actual->stub(), $actual->spy()->callback());
    }

    public function testEventOrderMethods()
    {
        $this->assertTrue((boolean) Phony::checkInOrder($this->eventA, $this->eventB));
        $this->assertFalse((boolean) Phony::checkInOrder($this->eventB, $this->eventA));
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            Phony::inOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) Phony::checkInOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) Phony::checkInOrderSequence(array($this->eventB, $this->eventA)));
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            Phony::inOrderSequence(array($this->eventA, $this->eventB))
        );
        $this->assertTrue((boolean) Phony::checkAnyOrder($this->eventA, $this->eventB));
        $this->assertFalse((boolean) Phony::checkAnyOrder());
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            Phony::anyOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) Phony::checkAnyOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) Phony::checkAnyOrderSequence(array()));
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            Phony::anyOrderSequence(array($this->eventA, $this->eventB))
        );
    }

    public function testInOrderMethodFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        Phony::inOrder($this->eventB, $this->eventA);
    }

    public function testInOrderSequenceMethodFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        Phony::inOrderSequence(array($this->eventB, $this->eventA));
    }

    public function testAnyOrderMethodFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        Phony::anyOrder();
    }

    public function testAnyOrderSequenceMethodFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        Phony::anyOrderSequence(array());
    }

    public function testEventOrderFunctions()
    {
        $this->assertTrue((boolean) checkInOrder($this->eventA, $this->eventB));
        $this->assertFalse((boolean) checkInOrder($this->eventB, $this->eventA));
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            inOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) checkInOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) checkInOrderSequence(array($this->eventB, $this->eventA)));
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            inOrderSequence(array($this->eventA, $this->eventB))
        );
        $this->assertTrue((boolean) checkAnyOrder($this->eventA, $this->eventB));
        $this->assertFalse((boolean) checkAnyOrder());
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            anyOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) checkAnyOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) checkAnyOrderSequence(array()));
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            anyOrderSequence(array($this->eventA, $this->eventB))
        );
    }

    public function testInOrderFunctionFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        inOrder($this->eventB, $this->eventA);
    }

    public function testInOrderSequenceFunctionFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        inOrderSequence(array($this->eventB, $this->eventA));
    }

    public function testAnyOrderFunctionFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        anyOrder();
    }

    public function testAnyOrderSequenceFunctionFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        anyOrderSequence(array());
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
}
