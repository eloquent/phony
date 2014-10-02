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
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use RuntimeException;

class CallTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->reflector = new ReflectionMethod(__METHOD__);
        $this->thisValue = $this;
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
        $this->sequenceNumber = 111;
        $this->startTime = 1.11;
        $this->calledEvent = new CalledEvent(
            $this->reflector,
            $this->thisValue,
            $this->arguments,
            $this->sequenceNumber,
            $this->startTime
        );
        $this->returnValue = 'returnValue';
        $this->endTime = 2.22;
        $this->returnedEvent = new ReturnedEvent($this->returnValue, $this->sequenceNumber + 1, $this->endTime);
        $this->eventA = new TestCallEvent($this->sequenceNumber + 2, 3.33);
        $this->eventB = new TestCallEvent($this->sequenceNumber + 3, 4.44);
        $this->events = array($this->calledEvent, $this->returnedEvent, $this->eventA, $this->eventB);
        $this->subject = new Call($this->events);

        $this->exception = new RuntimeException();
        $this->threwEvent = new ThrewEvent($this->exception, $this->sequenceNumber + 1, $this->endTime);
        $this->otherEvents = array($this->eventA, $this->eventB);
    }

    public function testConstructorWithReturnedEvent()
    {
        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
        $this->assertSame($this->reflector, $this->subject->reflector());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->startTime, $this->subject->startTime());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertSame($this->endTime, $this->subject->endTime());
    }

    public function testConstructorWithThrewEvent()
    {
        $this->events = array($this->calledEvent, $this->threwEvent, $this->eventA, $this->eventB);
        $this->subject = new Call($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
        $this->assertSame($this->reflector, $this->subject->reflector());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->startTime, $this->subject->startTime());
        $this->assertNull($this->subject->returnValue());
        $this->assertSame($this->exception, $this->subject->exception());
        $this->assertSame($this->endTime, $this->subject->endTime());
    }

    public function testConstructorWithNoEndEvent()
    {
        $this->events = array($this->calledEvent, $this->eventA, $this->eventB);
        $this->subject = new Call($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
        $this->assertSame($this->reflector, $this->subject->reflector());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->startTime, $this->subject->startTime());
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
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->events = array($this->calledEvent);
        $this->otherEvents = array();
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->endEvent());
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
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array($this->eventA, $this->threwEvent));
        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array());
        $this->events = array($this->calledEvent);
        $this->otherEvents = array();

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->endEvent());
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
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvent($this->eventA);
        $this->subject->addEvent($this->threwEvent);
        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
    }
}
