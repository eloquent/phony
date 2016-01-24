<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;

class EventCollectionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->eventA = $this->callEventFactory->createReturned();
        $this->eventB = $this->callFactory->create($this->callEventFactory->createCalled(null, array('a', 'b')));
        $this->eventC = $this->callEventFactory->createCalled(null, array('c', 'd'));
        $this->eventD = $this->callFactory->create($this->callEventFactory->createCalled(null, array('e', 'f')));
        $this->events = array($this->eventA, $this->eventB, $this->eventC, $this->eventD);
        $this->subject = new EventCollection($this->events);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->subject->hasEvents());
        $this->assertTrue($this->subject->hasCalls());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertSame(array($this->eventB, $this->eventD), $this->subject->allCalls());
        $this->assertSame(2, $this->subject->callCount());
        $this->assertSame(4, $this->subject->eventCount());
        $this->assertSame(4, count($this->subject));
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EventCollection();

        $this->assertFalse($this->subject->hasEvents());
        $this->assertFalse($this->subject->hasCalls());
        $this->assertSame(array(), $this->subject->allEvents());
        $this->assertSame(array(), $this->subject->allCalls());
        $this->assertSame(0, $this->subject->callCount());
        $this->assertSame(0, $this->subject->eventCount());
        $this->assertSame(0, count($this->subject));
    }

    public function testEventAt()
    {
        $this->assertSame($this->eventA, $this->subject->eventAt());
        $this->assertSame($this->eventA, $this->subject->eventAt(0));
        $this->assertSame($this->eventB, $this->subject->eventAt(1));
        $this->assertSame($this->eventB, $this->subject->eventAt(-3));
    }

    public function testEventAtFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->eventAt(111);
    }

    public function testFirstCall()
    {
        $this->assertSame($this->eventB, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->subject = new EventCollection();

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->assertSame($this->eventD, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->subject = new EventCollection();

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->lastCall();
    }

    public function testCallAt()
    {
        $this->assertSame($this->eventB, $this->subject->callAt());
        $this->assertSame($this->eventB, $this->subject->callAt(0));
        $this->assertSame($this->eventD, $this->subject->callAt(1));
        $this->assertSame($this->eventD, $this->subject->callAt(-1));
    }

    public function testCallAtFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->callAt(111);
    }

    public function testArguments()
    {
        $this->assertEquals(new Arguments(array('a', 'b')), $this->subject->arguments());

        $this->subject = new EventCollection(array($this->eventA, $this->eventD));

        $this->assertEquals(new Arguments(array('e', 'f')), $this->subject->arguments());

        $this->subject = new EventCollection(array($this->eventA));
    }

    public function testArgumentsFailureNoCalls()
    {
        $this->subject = new EventCollection(array($this->eventA));

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->arguments();
    }

    public function testArgumentsFailureEmpty()
    {
        $this->subject = new EventCollection();

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->arguments();
    }

    public function testArgument()
    {
        $this->assertSame('a', $this->subject->argument());
        $this->assertSame('a', $this->subject->argument(0));
        $this->assertSame('b', $this->subject->argument(1));

        $this->subject = new EventCollection(array($this->eventA, $this->eventD));

        $this->assertSame('e', $this->subject->argument());
        $this->assertSame('e', $this->subject->argument(0));
        $this->assertSame('f', $this->subject->argument(1));
    }

    public function testArgumentFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->argument(111);
    }

    public function testArgumentFailureNoCalls()
    {
        $this->subject = new EventCollection(array($this->eventA));

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->argument();
    }

    public function testArgumentFailureNoEvents()
    {
        $this->subject = new EventCollection();

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->argument();
    }

    public function testIteration()
    {
        $this->assertSame($this->events, iterator_to_array($this->subject));
    }
}
