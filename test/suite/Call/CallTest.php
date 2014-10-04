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
use Eloquent\Phony\Call\Event\SentValueEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
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
        $this->generatorEventA = new SentValueEvent(3, 3.0, 'c');
        $this->generatorEventB = new SentValueEvent(4, 4.0, 'd');
        $this->generatorEvents = array($this->generatorEventA, $this->generatorEventB);
        $this->subject = new Call($this->calledEvent, $this->returnedEvent, $this->generatorEvents);

        $this->events = array($this->calledEvent, $this->returnedEvent, $this->generatorEventA, $this->generatorEventB);
        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callFactory->createThrewEvent($this->exception);
    }

    public function testConstructorWithReturnedEvent()
    {
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->generatorEvents, $this->subject->generatorEvents());
        $this->assertSame($this->events, $this->subject->events());
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
        $this->subject = new Call($this->calledEvent, $this->threwEvent, $this->generatorEvents);
        $this->events = array($this->calledEvent, $this->threwEvent, $this->generatorEventA, $this->generatorEventB);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->responseEvent());
        $this->assertSame($this->generatorEvents, $this->subject->generatorEvents());
        $this->assertSame($this->events, $this->subject->events());
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
        $this->subject = new Call($this->calledEvent, null, $this->generatorEvents);
        $this->events = array($this->calledEvent, $this->generatorEventA, $this->generatorEventB);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->responseEvent());
        $this->assertSame($this->generatorEvents, $this->subject->generatorEvents());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->startTime());
        $this->assertNull($this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertNull($this->subject->endTime());
    }

    public function testSetResponseEvent()
    {
        $this->subject = new Call($this->calledEvent);
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
    }

    public function testSetResponseEventFailureAlreadySet()
    {
        $this->setExpectedException('InvalidArgumentException', 'Call already completed.');
        $this->subject->setResponseEvent($this->returnedEvent);
    }

    public function testAddGeneratorEvent()
    {
        $this->subject->addGeneratorEvent($this->generatorEventA);
        $this->generatorEvents = array($this->generatorEventA, $this->generatorEventB, $this->generatorEventA);

        $this->assertSame($this->generatorEvents, $this->subject->generatorEvents());
    }
}
