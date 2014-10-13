<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony;

use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Stub\Stub;
use Eloquent\Phony\Stub\StubVerifier;
use Eloquent\Phony\Test\TestEvent;
use PHPUnit_Framework_TestCase;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->eventA = new TestEvent(0, 0.0);
        $this->eventB = new TestEvent(1, 1.0);
    }

    public function testSpy()
    {
        $callback = function () {};
        $expected = new SpyVerifier(new Spy($callback));
        $actual = Phony::spy($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->callback());
    }

    public function testSpyFunction()
    {
        $callback = function () {};
        $expected = new SpyVerifier(new Spy($callback));
        $actual = spy($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->callback());
    }

    public function testStub()
    {
        $callback = function () {};
        $expected = new StubVerifier(new Stub($callback));
        $actual = Phony::stub($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->stub()->callback());
        $this->assertSame($actual->stub(), $actual->spy()->callback());
    }

    public function testStubFunction()
    {
        $callback = function () {};
        $expected = new StubVerifier(new Stub($callback));
        $actual = stub($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->stub()->callback());
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
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        inOrder($this->eventB, $this->eventA);
    }

    public function testInOrderSequenceFunctionFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
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
