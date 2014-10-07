<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

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
        $this->arguments = array('a', 'b');
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnValue = 'ab';
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->subject = new Call($this->calledEvent, $this->returnedEvent);

        $this->events = array($this->calledEvent, $this->returnedEvent);
    }

    public function testConstructorWithReturnedEvent()
    {
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->generatorEvents());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->startTime());
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
        $this->assertSame($threwEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->generatorEvents());
        $this->assertSame($threwEvent, $this->subject->endEvent());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->startTime());
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
        $this->assertNull($this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->generatorEvents());
        $this->assertNull($this->subject->endEvent());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertFalse($this->subject->hasResponded());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertFalse($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->startTime());
        $this->assertNull($this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertNull($this->subject->responseTime());
        $this->assertNull($this->subject->endTime());
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
