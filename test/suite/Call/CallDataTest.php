<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use AllowDynamicProperties;
use ArrayIterator;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use RuntimeException;

#[AllowDynamicProperties]
class CallDataTest extends TestCase
{
    protected function setUp(): void
    {
        $this->index = 111;
        $this->callFactory = new TestCallFactory();
        $this->eventFactory = $this->callFactory->eventFactory();
        $this->callback = 'implode';
        $this->parameters = (new ReflectionFunction('implode'))->getParameters();
        $this->arguments = new Arguments(['a', 'b']);
        $this->calledEvent = $this->eventFactory->createCalled($this->callback, $this->parameters, $this->arguments);
        $this->subject = new CallData($this->index, $this->calledEvent);

        $this->events = [$this->calledEvent];

        $this->returnValue = 'ab';
        $this->returnedEvent = $this->eventFactory->createReturned($this->returnValue);

        $this->exception = new Exception();
        $this->threwEvent = $this->eventFactory->createThrew($this->exception);

        $this->generator = GeneratorFactory::createEmpty();
        $this->generatedEvent = $this->eventFactory->createReturned($this->generator);
    }

    public function testConstructor()
    {
        $this->assertSame($this->index, $this->subject->index());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->subject, $this->subject->calledEvent()->call());
        $this->assertTrue($this->subject->hasCalls());
        $this->assertSame(1, $this->subject->eventCount());
        $this->assertSame(1, $this->subject->callCount());
        $this->assertCount(1, $this->subject);
        $this->assertNull($this->subject->responseEvent());
        $this->assertSame([], $this->subject->iterableEvents());
        $this->assertNull($this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame([$this->subject], $this->subject->allCalls());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertFalse($this->subject->hasResponded());
        $this->assertFalse($this->subject->isIterable());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertFalse($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->parameters, $this->subject->parameters());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame('a', $this->subject->argument());
        $this->assertSame('a', $this->subject->argument(0));
        $this->assertSame('b', $this->subject->argument(1));
        $this->assertSame('b', $this->subject->argument(-1));
        $this->assertSame('a', $this->subject->argument(-2));
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertEquals($this->calledEvent->time(), $this->subject->time());
        $this->assertNull($this->subject->responseTime());
        $this->assertNull($this->subject->endTime());
    }

    public function testReturnValueFailureThrew()
    {
        $exception = new RuntimeException('You done goofed.');
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($this->eventFactory->createThrew($exception));

        $this->expectException(UndefinedResponseException::class);
        $this->subject->returnValue();
    }

    public function testReturnValueFailureNoResponse()
    {
        $this->subject = new CallData($this->index, $this->calledEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->returnValue();
    }

    public function testExceptionFailureReturned()
    {
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->exception();
    }

    public function testExceptionFailureNoResponse()
    {
        $this->subject = new CallData($this->index, $this->calledEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->exception();
    }

    public function testResponse()
    {
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->assertSame([null, $this->returnValue], $this->subject->response());
    }

    public function testResponseWithThrewResponse()
    {
        $exception = new RuntimeException('You done goofed.');
        $threwEvent = $this->eventFactory->createThrew($exception);
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($threwEvent);
        $this->subject->setEndEvent($threwEvent);

        $this->assertSame([$exception, null], $this->subject->response());
    }

    public function testResponseFailureNoResponse()
    {
        $this->subject = new CallData($this->index, $this->calledEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->response();
    }

    public function testFirstEvent()
    {
        $this->assertSame($this->calledEvent, $this->subject->firstEvent());
    }

    public function testLastEvent()
    {
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subject->lastEvent());
    }

    public function testLastEventWithOnlyCalled()
    {
        $this->subject = new CallData($this->index, $this->calledEvent);

        $this->assertSame($this->calledEvent, $this->subject->lastEvent());
    }

    public function testLastEventWithIterable()
    {
        $this->returnValue = new ArrayIterator();
        $this->returnedEvent = $this->eventFactory->createReturned($this->returnValue);
        $this->iterableEventA = $this->eventFactory->createProduced('a', 'b');
        $this->iterableEventB = $this->eventFactory->createProduced('c', 'd');
        $this->consumedEvent = $this->eventFactory->createConsumed();
        $this->iterableEvents = [$this->iterableEventA, $this->iterableEventB];
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($this->returnedEvent);
        $this->subject->addIterableEvent($this->iterableEventA);
        $this->subject->addIterableEvent($this->iterableEventB);
        $this->subject->setEndEvent($this->consumedEvent);

        $this->assertSame($this->consumedEvent, $this->subject->lastEvent());
    }

    public function testLastEventWithUnconsumedIterable()
    {
        $this->returnValue = new ArrayIterator();
        $this->returnedEvent = $this->eventFactory->createReturned($this->returnValue);
        $this->iterableEventA = $this->eventFactory->createProduced('a', 'b');
        $this->iterableEventB = $this->eventFactory->createProduced('c', 'd');
        $this->iterableEvents = [$this->iterableEventA, $this->iterableEventB];
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($this->returnedEvent);
        $this->subject->addIterableEvent($this->iterableEventA);
        $this->subject->addIterableEvent($this->iterableEventB);

        $this->assertSame($this->iterableEventB, $this->subject->lastEvent());
    }

    public function testLastEventWithUniteratedIterable()
    {
        $this->returnValue = new ArrayIterator();
        $this->returnedEvent = $this->eventFactory->createReturned($this->returnValue);
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subject->lastEvent());
    }

    public function testEventAt()
    {
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->calledEvent, $this->subject->eventAt());
        $this->assertSame($this->calledEvent, $this->subject->eventAt(0));
        $this->assertSame($this->returnedEvent, $this->subject->eventAt(1));
        $this->assertSame($this->returnedEvent, $this->subject->eventAt(-1));
    }

    public function testEventAtFailure()
    {
        $this->expectException(UndefinedEventException::class);
        $this->subject->eventAt(2);
    }

    public function testFirstCall()
    {
        $this->assertSame($this->subject, $this->subject->firstCall());
    }

    public function testLastCall()
    {
        $this->assertSame($this->subject, $this->subject->lastCall());
    }

    public function testCallAt()
    {
        $this->assertSame($this->subject, $this->subject->callAt());
        $this->assertSame($this->subject, $this->subject->callAt(0));
        $this->assertSame($this->subject, $this->subject->callAt(-1));
    }

    public function testCallAtFailure()
    {
        $this->expectException(UndefinedCallException::class);
        $this->subject->callAt(1);
    }

    public function testIteration()
    {
        $this->assertSame([$this->subject], iterator_to_array($this->subject));
    }

    public function testAddIterableEvent()
    {
        $returnedEvent = $this->eventFactory->createReturned(['a' => 'b', 'c' => 'd']);
        $iterableEventA = $this->eventFactory->createProduced('a', 'b');
        $iterableEventB = $this->eventFactory->createProduced('c', 'd');
        $iterableEvents = [$iterableEventA, $iterableEventB];
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($returnedEvent);
        $this->subject->addIterableEvent($iterableEventA);
        $this->subject->addIterableEvent($iterableEventB);

        $this->assertSame($iterableEvents, $this->subject->iterableEvents());
        $this->assertSame($this->subject, $iterableEventA->call());
        $this->assertSame($this->subject, $iterableEventB->call());
    }

    public function testAddIterableEventFailureNotIterable()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not an iterable call.');
        $this->subject->addIterableEvent($this->eventFactory->createReceived('e'));
    }

    public function testAddIterableEventFailureAlreadyCompleted()
    {
        $returnedEvent = $this->eventFactory->createReturned([]);
        $endEvent = $this->eventFactory->createConsumed();
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($returnedEvent);
        $this->subject->setEndEvent($endEvent);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Call already completed.');
        $this->subject->addIterableEvent($this->eventFactory->createProduced('a', 'b'));
    }

    public function testSetResponseEventWithReturnedEvent()
    {
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->subject, $this->subject->responseEvent()->call());
        $this->assertNull($this->subject->endEvent());
    }

    public function testSetResponseEventFailureAlreadySet()
    {
        $this->subject->setResponseEvent($this->returnedEvent);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Call already responded.');
        $this->subject->setResponseEvent($this->returnedEvent);
    }

    public function testSetEndEventFailureAlreadySet()
    {
        $this->subject->setEndEvent($this->returnedEvent);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Call already completed.');
        $this->subject->setEndEvent($this->returnedEvent);
    }

    public function testCompareSequential()
    {
        $calledEventA = $this->eventFactory->createCalled($this->callback, $this->parameters, $this->arguments);
        $calledEventB = $this->eventFactory->createCalled($this->callback, $this->parameters, $this->arguments);
        $callA = new CallData(111, $calledEventA);
        $callB = new CallData(222, $calledEventB);

        $this->assertSame(-1, CallData::compareSequential($callA, $callB));
        $this->assertSame(1, CallData::compareSequential($callB, $callA));
        $this->assertSame(0, CallData::compareSequential($callA, $callA));
    }

    public function testConstructorWithGeneratedEventWithReturnEnd()
    {
        $generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->eventFactory->createProduced(null, null);
        $generatorEventB = $this->eventFactory->createReceived(null);
        $generatorEvents = [$generatorEventA, $generatorEventB];
        $endEvent = $this->eventFactory->createReturned(null);
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
        $generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->eventFactory->createProduced(null, null);
        $generatorEventB = $this->eventFactory->createReceived(null);
        $generatorEvents = [$generatorEventA, $generatorEventB];
        $exception = new RuntimeException('You done goofed.');
        $endEvent = $this->eventFactory->createThrew($exception);
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
        $generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->eventFactory->createProduced(null, null);
        $generatorEventB = $this->eventFactory->createReceived(null);
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
        $generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
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
        $generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);

        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($this->subject, $this->subject->responseEvent()->call());
        $this->assertNull($this->subject->endEvent());
    }

    public function testAddIterableEventWithGeneratorEvents()
    {
        $generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->eventFactory->createProduced(null, null);
        $generatorEventB = $this->eventFactory->createReceived(null);
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->subject->addIterableEvent($generatorEventA);
        $this->subject->addIterableEvent($generatorEventB);
        $generatorEvents = [$generatorEventA, $generatorEventB];

        $this->assertSame($generatorEvents, $this->subject->iterableEvents());
        $this->assertSame($this->subject, $generatorEventA->call());
        $this->assertSame($this->subject, $generatorEventB->call());
    }

    public function testAddIterableEventWithGeneratorEventsFailureAlreadyCompleted()
    {
        $generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $endEvent = $this->eventFactory->createReturned(null);
        $this->subject = new CallData(0, $this->calledEvent);
        $this->subject->setResponseEvent($generatedEvent);
        $this->subject->setEndEvent($endEvent);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Call already completed.');
        $this->subject->addIterableEvent($this->eventFactory->createReceived('e'));
    }

    public function testResponseMethodsWithGeneratorReturn()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->returnedEvent);

        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertSame($this->returnValue, $this->subject->generatorReturnValue());
        $this->assertSame([null, $this->returnValue], $this->subject->generatorResponse());
    }

    public function testResponseMethodsWithGeneratorException()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->threwEvent);

        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertSame([$this->exception, null], $this->subject->generatorResponse());
    }

    public function testGeneratorResponseFailureWithNonGeneratorReturn()
    {
        $this->subject->setResponseEvent($this->eventFactory->createReturned([]));
        $this->subject->setEndEvent($this->eventFactory->createConsumed());

        $this->assertTrue($this->subject->isIterable());
        $this->assertFalse($this->subject->isGenerator());

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorResponse();
    }

    public function testGeneratorResponseFailureWithoutEndEvent()
    {
        $this->subject->setResponseEvent($this->generatedEvent);

        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorResponse();
    }

    public function testGeneratorResponseFailureWithoutResponseEvent()
    {
        $this->assertFalse($this->subject->isIterable());
        $this->assertFalse($this->subject->isGenerator());

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorResponse();
    }

    public function testGeneratorReturnValueFailureWithGeneratorException()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->threwEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorReturnValue();
    }

    public function testGeneratorExceptionFailureWithGeneratorReturn()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->returnedEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorException();
    }
}
