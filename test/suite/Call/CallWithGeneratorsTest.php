<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\WithDynamicProperties;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Eloquent\Phony\Call\CallData
 */
class CallWithGeneratorsTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callback = 'implode';
        $this->arguments = new Arguments(['a', 'b']);
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->subject = new CallData(0, $this->calledEvent);

        $this->events = [$this->calledEvent];

        $this->returnValue = 'ab';
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
    }

    public function testConstructorWithGeneratedEventWithReturnEnd()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->callEventFactory->createProduced(null, null);
        $generatorEventB = $this->callEventFactory->createReceived(null);
        $generatorEvents = [$generatorEventA, $generatorEventB];
        $endEvent = $this->callEventFactory->createReturned(null);
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->subject->addIterableEvent($generatorEventA);
        $this->subject->addIterableEvent($generatorEventB);
        $this->subject->setEndEvent($endEvent);
        $this->events = [$this->calledEvent, $generatedEvent, $generatorEventA, $generatorEventB, $endEvent];

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($generatorEvents, $this->subject->iterableEvents());
        $this->assertSame($endEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf(Generator::class, $this->subject->returnValue());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertEquals($endEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithGeneratedEventWithThrowEnd()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->callEventFactory->createProduced(null, null);
        $generatorEventB = $this->callEventFactory->createReceived(null);
        $generatorEvents = [$generatorEventA, $generatorEventB];
        $exception = new RuntimeException('You done goofed.');
        $endEvent = $this->callEventFactory->createThrew($exception);
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->subject->addIterableEvent($generatorEventA);
        $this->subject->addIterableEvent($generatorEventB);
        $this->subject->setEndEvent($endEvent);
        $this->events = [$this->calledEvent, $generatedEvent, $generatorEventA, $generatorEventB, $endEvent];

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($generatorEvents, $this->subject->iterableEvents());
        $this->assertSame($endEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf(Generator::class, $this->subject->returnValue());
        $this->assertSame($exception, $this->subject->generatorException());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertEquals($endEvent->time(), $this->subject->endTime());
    }

    public function testConstructorWithGeneratedEventWithoutEnd()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->callEventFactory->createProduced(null, null);
        $generatorEventB = $this->callEventFactory->createReceived(null);
        $generatorEvents = [$generatorEventA, $generatorEventB];
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->subject->addIterableEvent($generatorEventA);
        $this->subject->addIterableEvent($generatorEventB);
        $this->events = [$this->calledEvent, $generatedEvent, $generatorEventA, $generatorEventB];

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($generatorEvents, $this->subject->iterableEvents());
        $this->assertNull($this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertFalse($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf(Generator::class, $this->subject->returnValue());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertNull($this->subject->endTime());
    }

    public function testConstructorWithGeneratedEventWithoutGeneratorEvents()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->events = [$this->calledEvent, $generatedEvent];

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame([], $this->subject->iterableEvents());
        $this->assertNull($this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertFalse($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertInstanceOf(Generator::class, $this->subject->returnValue());
        $this->assertEquals($generatedEvent->time(), $this->subject->responseTime());
        $this->assertNull($this->subject->endTime());
    }

    public function testSetResponseEventWithGeneratedEvent()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);

        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($this->subject, $this->subject->responseEvent()->call());
        $this->assertNull($this->subject->endEvent());
    }

    public function testAddIterableEvent()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->callEventFactory->createProduced(null, null);
        $generatorEventB = $this->callEventFactory->createReceived(null);
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->subject->addIterableEvent($generatorEventA);
        $this->subject->addIterableEvent($generatorEventB);
        $generatorEvents = [$generatorEventA, $generatorEventB];

        $this->assertSame($generatorEvents, $this->subject->iterableEvents());
        $this->assertSame($this->subject, $generatorEventA->call());
        $this->assertSame($this->subject, $generatorEventB->call());
    }

    public function testAddIterableEventFailureAlreadyCompleted()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $endEvent = $this->callEventFactory->createReturned(null);
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->subject->setEndEvent($endEvent);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Call already completed.');
        $this->subject->addIterableEvent($this->callEventFactory->createReceived('e'));
    }
}
