<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use ArrayIterator;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Test\TestCallFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CallDataTest extends TestCase
{
    protected function setUp()
    {
        $this->index = 111;
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callback = 'implode';
        $this->arguments = new Arguments(['a', 'b']);
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->subject = new CallData($this->index, $this->calledEvent);

        $this->events = [$this->calledEvent];

        $this->returnValue = 'ab';
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
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
        $this->subject->setResponseEvent($this->callEventFactory->createThrew($exception));

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
        $threwEvent = $this->callEventFactory->createThrew($exception);
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
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->iterableEventA = $this->callEventFactory->createProduced('a', 'b');
        $this->iterableEventB = $this->callEventFactory->createProduced('c', 'd');
        $this->consumedEvent = $this->callEventFactory->createConsumed();
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
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->iterableEventA = $this->callEventFactory->createProduced('a', 'b');
        $this->iterableEventB = $this->callEventFactory->createProduced('c', 'd');
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
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
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
        $returnedEvent = $this->callEventFactory->createReturned(['a' => 'b', 'c' => 'd']);
        $iterableEventA = $this->callEventFactory->createProduced('a', 'b');
        $iterableEventB = $this->callEventFactory->createProduced('c', 'd');
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
        $this->subject->addIterableEvent($this->callEventFactory->createReceived('e'));
    }

    public function testAddIterableEventFailureAlreadyCompleted()
    {
        $returnedEvent = $this->callEventFactory->createReturned([]);
        $endEvent = $this->callEventFactory->createConsumed();
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setResponseEvent($returnedEvent);
        $this->subject->setEndEvent($endEvent);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Call already completed.');
        $this->subject->addIterableEvent($this->callEventFactory->createProduced('a', 'b'));
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

    public function testSetEndEventWithReturnedEvent()
    {
        $this->subject = new CallData($this->index, $this->calledEvent);
        $this->subject->setEndEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->subject, $this->subject->endEvent()->call());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame($this->subject, $this->subject->responseEvent()->call());
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
        $calledEventA = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $calledEventB = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $callA = new CallData(111, $calledEventA);
        $callB = new CallData(222, $calledEventB);

        $this->assertSame(-1, CallData::compareSequential($callA, $callB));
        $this->assertSame(1, CallData::compareSequential($callB, $callA));
        $this->assertSame(0, CallData::compareSequential($callA, $callA));
    }
}
