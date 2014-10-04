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

use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Test\TestCallEvent;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class CallTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callback = 'implode';
        $this->arguments = array('a', 'b');
        $this->returnValue = 'ab';
        $this->calledEvent = $this->callFactory->createCalledEvent($this->callback, $this->arguments);
        $this->returnedEvent = $this->callFactory->createReturnedEvent($this->returnValue);
        $this->eventA = new TestCallEvent(3, 3.0);
        $this->eventB = new TestCallEvent(4, 4.0);
        $this->events = array($this->calledEvent, $this->returnedEvent, $this->eventA, $this->eventB);
        $this->otherEvents = array($this->eventA, $this->eventB);
        $this->subject = new Call($this->events);

        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callFactory->createThrewEvent($this->exception);
    }

    public function testConstructorWithReturnedEvent()
    {
        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->startTime());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertEquals($this->returnedEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithThrewEvent()
    {
        $this->events = array($this->calledEvent, $this->threwEvent, $this->eventA, $this->eventB);
        $this->subject = new Call($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->startTime());
        $this->assertNull($this->subject->returnValue());
        $this->assertSame($this->exception, $this->subject->exception());
        $this->assertEquals($this->threwEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithNoResponseEvent()
    {
        $this->events = array($this->calledEvent, $this->eventA, $this->eventB);
        $this->subject = new Call($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->startTime());
        $this->assertNull($this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertNull($this->subject->endTime());
    }

    public function testConstructorFailureNoFirstCalledEvent()
    {
        $this->events = array($this->eventA, $this->calledEvent, $this->returnedEvent);

        $this->setExpectedException(
            'InvalidArgumentException',
            'Calls must have at least one event, and the first event must be an instance of ' .
                'Eloquent\Phony\Call\Event\CalledEventInterface.'
        );
        $this->subject = new Call($this->events);
    }

    public function testSetEvents()
    {
        $this->events = array($this->calledEvent, $this->eventA, $this->returnedEvent);
        $this->otherEvents = array($this->eventA);
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->events = array($this->calledEvent);
        $this->otherEvents = array();
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
    }

    public function testAddEvents()
    {
        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array($this->eventA, $this->returnedEvent));
        $this->events = array($this->calledEvent, $this->eventA, $this->returnedEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array($this->eventA, $this->threwEvent));
        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array());
        $this->events = array($this->calledEvent);
        $this->otherEvents = array();

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
    }

    public function testAddEvent()
    {
        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvent($this->eventA);
        $this->subject->addEvent($this->returnedEvent);
        $this->events = array($this->calledEvent, $this->eventA, $this->returnedEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvent($this->eventA);
        $this->subject->addEvent($this->threwEvent);
        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->responseEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
    }
}
