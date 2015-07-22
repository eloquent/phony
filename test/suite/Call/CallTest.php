<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use ArrayIterator;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class CallTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callback = 'implode';
        $this->arguments = new Arguments(array('a', 'b'));
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnValue = 'ab';
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->indexNormalizer = new IndexNormalizer();
        $this->subject = new Call($this->calledEvent, $this->returnedEvent, null, null, $this->indexNormalizer);

        $this->events = array($this->calledEvent, $this->returnedEvent);
    }

    public function testConstructorWithReturnedEvent()
    {
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertTrue($this->subject->hasCalls());
        $this->assertSame(2, $this->subject->eventCount());
        $this->assertSame(1, $this->subject->callCount());
        $this->assertSame(2, count($this->subject));
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->traversableEvents());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame(array($this->subject), $this->subject->allCalls());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertFalse($this->subject->isTraversable());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame('a', $this->subject->argument());
        $this->assertSame('a', $this->subject->argument(0));
        $this->assertSame('b', $this->subject->argument(1));
        $this->assertSame('b', $this->subject->argument(-1));
        $this->assertSame('a', $this->subject->argument(-2));
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertEquals($this->returnedEvent->time(), $this->subject->responseTime());
        $this->assertEquals($this->returnedEvent->time(), $this->subject->endTime());
        $this->assertSame($this->indexNormalizer, $this->subject->indexNormalizer());
    }

    public function testConstructorWithReturnedArrayEvent()
    {
        $exception = new RuntimeException('You done goofed.');
        $this->returnValue = array();
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->subject = new Call($this->calledEvent, $this->returnedEvent);
        $this->events = array($this->calledEvent, $this->returnedEvent);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame(2, count($this->subject));
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->traversableEvents());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isTraversable());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertEquals($this->returnedEvent->time(), $this->subject->responseTime());
        $this->assertEquals($this->returnedEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithReturnedTraversableEvent()
    {
        $exception = new RuntimeException('You done goofed.');
        $this->returnValue = new ArrayIterator();
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->subject = new Call($this->calledEvent, $this->returnedEvent);
        $this->events = array($this->calledEvent, $this->returnedEvent);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame(2, count($this->subject));
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->traversableEvents());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isTraversable());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertEquals($this->returnedEvent->time(), $this->subject->responseTime());
        $this->assertEquals($this->returnedEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithThrewEvent()
    {
        $exception = new RuntimeException('You done goofed.');
        $threwEvent = $this->callEventFactory->createThrew($exception);
        $this->subject = new Call($this->calledEvent, $threwEvent);
        $this->events = array($this->calledEvent, $threwEvent);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame(2, count($this->subject));
        $this->assertSame($threwEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->traversableEvents());
        $this->assertSame($threwEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertFalse($this->subject->isTraversable());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertNull($this->subject->returnValue());
        $this->assertSame($exception, $this->subject->exception());
        $this->assertEquals($threwEvent->time(), $this->subject->responseTime());
        $this->assertEquals($threwEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithNoResponseEvent()
    {
        $this->subject = new Call($this->calledEvent);
        $this->events = array($this->calledEvent);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame(1, count($this->subject));
        $this->assertNull($this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->traversableEvents());
        $this->assertNull($this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertFalse($this->subject->hasResponded());
        $this->assertFalse($this->subject->isTraversable());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertFalse($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertNull($this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertNull($this->subject->responseTime());
        $this->assertNull($this->subject->endTime());
    }

    public function testConstructorDefaultIndexNormalizer()
    {
        $this->subject = new Call($this->calledEvent);

        $this->assertSame(IndexNormalizer::instance(), $this->subject->indexNormalizer());
    }

    public function testEventAt()
    {
        $this->assertSame($this->calledEvent, $this->subject->eventAt());
        $this->assertSame($this->calledEvent, $this->subject->eventAt(0));
        $this->assertSame($this->returnedEvent, $this->subject->eventAt(1));
        $this->assertSame($this->returnedEvent, $this->subject->eventAt(-1));
    }

    public function testEventAtFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->eventAt(2);
    }

    public function testCallAt()
    {
        $this->assertSame($this->subject, $this->subject->callAt());
        $this->assertSame($this->subject, $this->subject->callAt(0));
        $this->assertSame($this->subject, $this->subject->callAt(-1));
    }

    public function testCallAtFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->callAt(1);
    }

    public function testIteration()
    {
        $this->assertSame(array($this->subject), iterator_to_array($this->subject));
    }

    public function testAddTraversableEvent()
    {
        $returnedEvent = $this->callEventFactory->createReturned(array('a' => 'b', 'c' => 'd'));
        $traversableEventA = $this->callEventFactory->createProduced('a', 'b');
        $traversableEventB = $this->callEventFactory->createProduced('c', 'd');
        $this->subject = new Call($this->calledEvent, $returnedEvent);
        $this->subject->addTraversableEvent($traversableEventA);
        $this->subject->addTraversableEvent($traversableEventB);
        $traversableEvents = array($traversableEventA, $traversableEventB);

        $this->assertSame($traversableEvents, $this->subject->traversableEvents());
        $this->assertSame($this->subject, $traversableEventA->call());
        $this->assertSame($this->subject, $traversableEventB->call());
    }

    public function testAddTraversableEventFailureNotTraversable()
    {
        $this->setExpectedException('InvalidArgumentException', 'Not a traversable call.');
        $this->subject->addTraversableEvent($this->callEventFactory->createReceived('e'));
    }

    public function testSetResponseEventWithReturnedEvent()
    {
        $this->subject = new Call($this->calledEvent);
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->subject, $this->subject->responseEvent()->call());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->subject, $this->subject->endEvent()->call());
    }

    public function testSetResponseEventFailureAlreadySet()
    {
        $this->setExpectedException('InvalidArgumentException', 'Call already responded.');
        $this->subject->setResponseEvent($this->returnedEvent);
    }

    public function testSetEndEventWithReturnedEvent()
    {
        $this->subject = new Call($this->calledEvent);
        $this->subject->setEndEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->subject, $this->subject->endEvent()->call());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->subject, $this->subject->responseEvent()->call());
    }

    public function testSetEndEventFailureAlreadySet()
    {
        $this->setExpectedException('InvalidArgumentException', 'Call already completed.');
        $this->subject->setEndEvent($this->returnedEvent);
    }
}
