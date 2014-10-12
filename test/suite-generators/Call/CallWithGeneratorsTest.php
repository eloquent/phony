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

/**
 * @covers \Eloquent\Phony\Call\Call
 */
class CallWithGeneratorsTest extends PHPUnit_Framework_TestCase
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

    public function testConstructorWithGeneratedEventWithReturnEnd()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $generatorEventA = $this->callEventFactory->createProduced();
        $generatorEventB = $this->callEventFactory->createReceived();
        $generatorEvents = array($generatorEventA, $generatorEventB);
        $endEvent = $this->callEventFactory->createReturned();
        $this->subject = new Call($this->calledEvent, $generatedEvent, $generatorEvents, $endEvent);
        $this->events = array($this->calledEvent, $generatedEvent, $generatorEventA, $generatorEventB, $endEvent);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($this->subject, $this->subject->firstEvent());
        $this->assertSame($endEvent, $this->subject->lastEvent());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($generatorEvents, $this->subject->traversableEvents());
        $this->assertSame($endEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isTraversable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf('Generator', $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertEquals($endEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithGeneratedEventWithThrowEnd()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $generatorEventA = $this->callEventFactory->createProduced();
        $generatorEventB = $this->callEventFactory->createReceived();
        $generatorEvents = array($generatorEventA, $generatorEventB);
        $exception = new RuntimeException('You done goofed.');
        $endEvent = $this->callEventFactory->createThrew($exception);
        $this->subject = new Call($this->calledEvent, $generatedEvent, $generatorEvents, $endEvent);
        $this->events = array($this->calledEvent, $generatedEvent, $generatorEventA, $generatorEventB, $endEvent);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($this->subject, $this->subject->firstEvent());
        $this->assertSame($endEvent, $this->subject->lastEvent());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($generatorEvents, $this->subject->traversableEvents());
        $this->assertSame($endEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isTraversable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf('Generator', $this->subject->returnValue());
        $this->assertSame($exception, $this->subject->exception());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertEquals($endEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithGeneratedEventWithoutEnd()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $generatorEventA = $this->callEventFactory->createProduced();
        $generatorEventB = $this->callEventFactory->createReceived();
        $generatorEvents = array($generatorEventA, $generatorEventB);
        $this->subject = new Call($this->calledEvent, $generatedEvent, $generatorEvents);
        $this->events = array($this->calledEvent, $generatedEvent, $generatorEventA, $generatorEventB);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($this->subject, $this->subject->firstEvent());
        $this->assertSame($generatorEventB, $this->subject->lastEvent());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($generatorEvents, $this->subject->traversableEvents());
        $this->assertNull($this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isTraversable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertFalse($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf('Generator', $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertNull($this->subject->endTime());
    }

    public function testConstructorWithGeneratedEventWithoutGeneratorEvents()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $this->subject = new Call($this->calledEvent, $generatedEvent);
        $this->events = array($this->calledEvent, $generatedEvent);

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($this->subject, $this->subject->firstEvent());
        $this->assertSame($generatedEvent, $this->subject->lastEvent());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->traversableEvents());
        $this->assertNull($this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isTraversable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertFalse($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf('Generator', $this->subject->returnValue());
        $this->assertNull($this->subject->exception());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertNull($this->subject->endTime());
    }

    public function testSetResponseEventWithGeneratedEvent()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $this->subject = new Call($this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);

        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($this->subject, $this->subject->responseEvent()->call());
        $this->assertNull($this->subject->endEvent());
    }

    public function testAddTraversableEvent()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $generatorEventA = $this->callEventFactory->createProduced();
        $generatorEventB = $this->callEventFactory->createReceived();
        $this->subject = new Call($this->calledEvent, $generatedEvent);
        $this->subject->addTraversableEvent($generatorEventA);
        $this->subject->addTraversableEvent($generatorEventB);
        $generatorEvents = array($generatorEventA, $generatorEventB);

        $this->assertSame($generatorEvents, $this->subject->traversableEvents());
        $this->assertSame($this->subject, $generatorEventA->call());
        $this->assertSame($this->subject, $generatorEventB->call());
    }

    public function testAddTraversableEventFailureAlreadyCompleted()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $endEvent = $this->callEventFactory->createReturned();
        $this->subject = new Call($this->calledEvent, $generatedEvent, array(), $endEvent);

        $this->setExpectedException('InvalidArgumentException', 'Call already completed.');
        $this->subject->addTraversableEvent($this->callEventFactory->createReceived('e'));
    }
}
