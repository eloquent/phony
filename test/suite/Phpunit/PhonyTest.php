<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Phpunit;

use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Integration\Phpunit\PhpunitAssertionRecorder;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Test\TestEvent;
use PHPUnit_Framework_TestCase;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->eventA = new TestEvent(0, 0.0);
        $this->eventB = new TestEvent(1, 1.0);
    }

    public function testMock()
    {
        $actual = Phony::mock('Eloquent\Phony\Test\TestClassA');

        $this->assertInstanceOf('Eloquent\Phony\Mock\Builder\MockBuilder', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->get());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $actual->get());
    }

    public function testMockFunction()
    {
        $actual = mock('Eloquent\Phony\Test\TestClassA');

        $this->assertInstanceOf('Eloquent\Phony\Mock\Builder\MockBuilder', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual->get());
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $actual->get());
    }

    public function testSpy()
    {
        $callback = function () {};
        $actual = Phony::spy($callback);

        $this->assertInstanceOf('Eloquent\Phony\Spy\SpyVerifier', $actual);
        $this->assertSame($callback, $actual->callback());
        $this->assertEquals(new PhpunitAssertionRecorder(), $actual->callVerifierFactory()->assertionRecorder());
    }

    public function testSpyFunction()
    {
        $callback = function () {};
        $actual = spy($callback);

        $this->assertInstanceOf('Eloquent\Phony\Spy\SpyVerifier', $actual);
        $this->assertSame($callback, $actual->callback());
        $this->assertEquals(new PhpunitAssertionRecorder(), $actual->callVerifierFactory()->assertionRecorder());
    }

    public function testStub()
    {
        $callback = function () {};
        $actual = Phony::stub($callback);

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($callback, $actual->stub()->callback());
        $this->assertSame($actual->stub(), $actual->spy()->callback());
        $this->assertEquals(new PhpunitAssertionRecorder(), $actual->callVerifierFactory()->assertionRecorder());
    }

    public function testStubFunction()
    {
        $callback = function () {};
        $actual = stub($callback);

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($callback, $actual->stub()->callback());
        $this->assertSame($actual->stub(), $actual->spy()->callback());
        $this->assertEquals(new PhpunitAssertionRecorder(), $actual->callVerifierFactory()->assertionRecorder());
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
            new EventCollection(array($this->eventA, $this->eventB)),
            inOrder($this->eventA, $this->eventB)
        );
        $this->assertTrue((boolean) checkInOrderSequence(array($this->eventA, $this->eventB)));
        $this->assertFalse((boolean) checkInOrderSequence(array($this->eventB, $this->eventA)));
        $this->assertEquals(
            new EventCollection(array($this->eventA, $this->eventB)),
            inOrderSequence(array($this->eventA, $this->eventB))
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
}
