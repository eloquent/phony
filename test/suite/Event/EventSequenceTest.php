<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class EventSequenceTest extends TestCase
{
    protected function setUp(): void
    {
        $container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $container->callFactory;
        $this->eventFactory = $container->eventFactory;
        $this->callVerifierFactory = $container->callVerifierFactory;

        $this->eventA = $this->eventFactory->createReturned(null);
        $this->eventB =
            $this->callFactory->create($this->eventFactory->createCalled(null, Arguments::create('a', 'b')));
        $this->eventC = $this->eventFactory->createCalled(null, Arguments::create('c', 'd'));
        $this->eventD =
            $this->callFactory->create($this->eventFactory->createCalled(null, Arguments::create('e', 'f')));
        $this->events = [$this->eventA, $this->eventB, $this->eventC, $this->eventD];
        $this->subject = new EventSequence($this->events, $this->callVerifierFactory);

        $this->wrappedCallB = $this->callVerifierFactory->fromCall($this->eventB);
        $this->wrappedCallD = $this->callVerifierFactory->fromCall($this->eventD);
        $this->wrappedCalls = [$this->wrappedCallB, $this->wrappedCallD];
    }

    public function testConstructor()
    {
        $this->assertTrue($this->subject->hasEvents());
        $this->assertTrue($this->subject->hasCalls());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertSame(2, $this->subject->callCount());
        $this->assertSame(4, $this->subject->eventCount());
        $this->assertCount(4, $this->subject);
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EventSequence([], $this->callVerifierFactory);

        $this->assertFalse($this->subject->hasEvents());
        $this->assertFalse($this->subject->hasCalls());
        $this->assertSame([], $this->subject->allEvents());
        $this->assertSame([], $this->subject->allCalls());
        $this->assertSame(0, $this->subject->callCount());
        $this->assertSame(0, $this->subject->eventCount());
        $this->assertCount(0, $this->subject);
    }

    public function testFirstEvent()
    {
        $this->assertSame($this->eventA, $this->subject->firstEvent());
    }

    public function testFirstEventFailureUndefined()
    {
        $this->subject = new EventSequence([], $this->callVerifierFactory);

        $this->expectException(UndefinedEventException::class);
        $this->subject->firstEvent();
    }

    public function testLastEvent()
    {
        $this->assertSame($this->eventD, $this->subject->lastEvent());
    }

    public function testLastEventFailureUndefined()
    {
        $this->subject = new EventSequence([], $this->callVerifierFactory);

        $this->expectException(UndefinedEventException::class);
        $this->subject->lastEvent();
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
        $this->expectException(UndefinedEventException::class);
        $this->subject->eventAt(111);
    }

    public function testFirstCall()
    {
        $this->assertEquals($this->wrappedCallB, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->subject = new EventSequence([], $this->callVerifierFactory);

        $this->expectException(UndefinedCallException::class);
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->assertEquals($this->wrappedCallD, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->subject = new EventSequence([], $this->callVerifierFactory);

        $this->expectException(UndefinedCallException::class);
        $this->subject->lastCall();
    }

    public function testAllCalls()
    {
        $this->assertEquals($this->wrappedCalls, $this->subject->allCalls());
    }

    public function testCallAt()
    {
        $this->assertEquals($this->wrappedCallB, $this->subject->callAt());
        $this->assertEquals($this->wrappedCallB, $this->subject->callAt(0));
        $this->assertEquals($this->wrappedCallD, $this->subject->callAt(1));
        $this->assertEquals($this->wrappedCallD, $this->subject->callAt(-1));
    }

    public function testCallAtFailure()
    {
        $this->expectException(UndefinedCallException::class);
        $this->subject->callAt(111);
    }

    public function testIteration()
    {
        $this->assertSame($this->events, iterator_to_array($this->subject));
    }
}
