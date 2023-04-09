<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use AllowDynamicProperties;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassA;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[AllowDynamicProperties]
class IterableVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;

        $this->callVerifierFactory = $this->container->callVerifierFactory;
        $this->matcherFactory = $this->container->matcherFactory;
        $this->featureDetector = $this->container->featureDetector;
        $this->exporter = $this->container->exporter;

        $this->iterableCalledEvent = $this->eventFactory->createCalled();
        $this->returnedIterableEvent =
            $this->eventFactory->createReturned(['m' => 'n', 'p' => 'q', 'r' => 's', 'u' => 'v']);
        $this->iteratorUsedEvent = $this->eventFactory->createUsed();
        $this->iteratorEventA = $this->eventFactory->createProduced('m', 'n');
        $this->iteratorEventC = $this->eventFactory->createProduced('p', 'q');
        $this->iteratorEventE = $this->eventFactory->createProduced('r', 's');
        $this->iteratorEventG = $this->eventFactory->createProduced('u', 'v');
        $this->iteratorEvents = [
            $this->iteratorUsedEvent,
            $this->iteratorEventA,
            $this->iteratorEventC,
            $this->iteratorEventE,
            $this->iteratorEventG,
        ];
        $this->iterableEndEvent = $this->eventFactory->createConsumed();
        $this->iteratorCall = $this->callFactory->create(
            $this->iterableCalledEvent,
            $this->returnedIterableEvent,
            $this->iteratorEvents,
            $this->iterableEndEvent
        );

        $this->returnedEvent = $this->eventFactory->createReturned(null);
        $this->calls = [
            $this->callFactory->create(
                null,
                $this->returnedEvent,
                [],
                $this->returnedEvent
            ),
            $this->iteratorCall,
        ];
        $this->nonIterableCalls = [
            $this->callFactory->create(),
            $this->callFactory->create(),
        ];

        $this->wrappedCalls = [
            $this->callVerifierFactory->fromCall($this->calls[0]),
            $this->callVerifierFactory->fromCall($this->calls[1]),
        ];

        $this->returnValueA = 'x';
        $this->returnValueB = 'y';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = new TestClassA();
        $this->thisValueB = new TestClassA();
        $this->arguments = Arguments::create('a', 'b', 'c');
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->eventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], [], $this->arguments),
            ($responseEvent = $this->eventFactory->createReturned($this->returnValueA)),
            null,
            $responseEvent
        );
        $this->callB = $this->callFactory->create(
            $this->eventFactory->createCalled([$this->thisValueB, 'testClassAMethodA']),
            ($responseEvent = $this->eventFactory->createReturned($this->returnValueB)),
            null,
            $responseEvent
        );
        $this->callC = $this->callFactory->create(
            $this->eventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], [], $this->arguments),
            ($responseEvent = $this->eventFactory->createThrew($this->exceptionA)),
            null,
            $responseEvent
        );
        $this->callD = $this->callFactory->create(
            $this->eventFactory->createCalled('implode'),
            ($responseEvent = $this->eventFactory->createThrew($this->exceptionB)),
            null,
            $responseEvent
        );
        $this->callE = $this->callFactory->create($this->eventFactory->createCalled('implode'));
        $this->typicalCalls = [$this->callA, $this->callB, $this->callC, $this->callD, $this->callE];

        $this->typicalCallsPlusIteratorCall = $this->typicalCalls;
        $this->typicalCallsPlusIteratorCall[] = $this->iteratorCall;
    }

    private function setUpWith($calls)
    {
        $this->spy = $this->container->spyFactory->create('implode')->setLabel('label');
        $this->spy->setCalls($calls);
        $this->subject = new IterableVerifier(
            $this->spy,
            $calls,
            $this->container->matcherFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
    }

    public function testConstructor()
    {
        $this->setUpWith([]);

        $this->assertEquals(new Cardinality(1, -1), $this->subject->cardinality());
    }

    public function testHasEvents()
    {
        $this->setUpWith([]);

        $this->assertFalse($this->subject->hasEvents());

        $this->setUpWith([$this->callA]);

        $this->assertTrue($this->subject->hasEvents());
    }

    public function testHasCalls()
    {
        $this->setUpWith([]);

        $this->assertFalse($this->subject->hasCalls());

        $this->setUpWith([$this->callA]);

        $this->assertTrue($this->subject->hasCalls());
    }

    public function testEventCount()
    {
        $this->setUpWith([]);

        $this->assertSame(0, $this->subject->eventCount());

        $this->setUpWith([$this->callA]);

        $this->assertSame(1, $this->subject->eventCount());
    }

    public function testCallCount()
    {
        $this->setUpWith([]);

        $this->assertSame(0, $this->subject->callCount());
        $this->assertCount(0, $this->subject);

        $this->setUpWith([$this->callA]);

        $this->assertSame(1, $this->subject->callCount());
        $this->assertCount(1, $this->subject);
    }

    public function testAllEvents()
    {
        $this->setUpWith([]);

        $this->assertSame([], $this->subject->allEvents());

        $this->setUpWith([$this->callA]);

        $this->assertSame([$this->callA], $this->subject->allEvents());
    }

    public function testAllCalls()
    {
        $this->setUpWith([]);

        $this->assertSame([], $this->subject->allCalls());

        $this->setUpWith($this->calls);

        $this->assertEquals($this->wrappedCalls, $this->subject->allCalls());
        $this->assertEquals($this->wrappedCalls, iterator_to_array($this->subject));
    }

    public function testFirstEvent()
    {
        $this->setUpWith($this->calls);

        $this->assertSame($this->calls[0], $this->subject->firstEvent());
    }

    public function testFirstEventFailureUndefined()
    {
        $this->setUpWith([]);

        $this->expectException(UndefinedEventException::class);
        $this->subject->firstEvent();
    }

    public function testLastEvent()
    {
        $this->setUpWith($this->calls);

        $this->assertSame($this->calls[1], $this->subject->lastEvent());
    }

    public function testLastEventFailureUndefined()
    {
        $this->setUpWith([]);

        $this->expectException(UndefinedEventException::class);
        $this->subject->lastEvent();
    }

    public function testEventAt()
    {
        $this->setUpWith([$this->callA]);

        $this->assertSame($this->callA, $this->subject->eventAt());
        $this->assertSame($this->callA, $this->subject->eventAt(0));
        $this->assertSame($this->callA, $this->subject->eventAt(-1));
    }

    public function testEventAtFailure()
    {
        $this->setUpWith([]);

        $this->expectException(UndefinedEventException::class);
        $this->subject->eventAt();
    }

    public function testFirstCall()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals($this->wrappedCalls[0], $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->setUpWith([]);

        $this->expectException(UndefinedCallException::class);
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals($this->wrappedCalls[1], $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->setUpWith([]);

        $this->expectException(UndefinedCallException::class);
        $this->subject->lastCall();
    }

    public function testCallAt()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals($this->wrappedCalls[0], $this->subject->callAt(0));
        $this->assertEquals($this->wrappedCalls[1], $this->subject->callAt(1));
    }

    public function testCallAtFailureUndefined()
    {
        $this->setUpWith([]);

        $this->expectException(UndefinedCallException::class);
        $this->subject->callAt(0);
    }

    public function testCheckUsed()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkUsed());
        $this->assertFalse((bool) $this->subject->times(1)->checkUsed());
        $this->assertFalse((bool) $this->subject->once()->checkUsed());
        $this->assertTrue((bool) $this->subject->never()->checkUsed());

        $this->setUpWith($this->nonIterableCalls);

        $this->assertFalse((bool) $this->subject->checkUsed());
        $this->assertFalse((bool) $this->subject->times(1)->checkUsed());
        $this->assertFalse((bool) $this->subject->once()->checkUsed());
        $this->assertTrue((bool) $this->subject->never()->checkUsed());

        $this->setUpWith($this->calls);

        $this->assertTrue((bool) $this->subject->checkUsed());
        $this->assertTrue((bool) $this->subject->times(1)->checkUsed());
        $this->assertTrue((bool) $this->subject->once()->checkUsed());
        $this->assertFalse((bool) $this->subject->never()->checkUsed());
        $this->assertFalse((bool) $this->subject->always()->checkUsed());

        $this->setUpWith([$this->iteratorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkUsed());
    }

    public function testUsed()
    {
        $this->setUpWith([]);

        $this->assertEquals(new EventSequence([], $this->callVerifierFactory), $this->subject->never()->used());

        $this->setUpWith($this->nonIterableCalls);

        $this->assertEquals(new EventSequence([], $this->callVerifierFactory), $this->subject->never()->used());

        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence([$this->iteratorUsedEvent], $this->callVerifierFactory),
            $this->subject->used()
        );
        $this->assertEquals(
            new EventSequence([$this->iteratorUsedEvent], $this->callVerifierFactory),
            $this->subject->times(1)->used()
        );
        $this->assertEquals(
            new EventSequence([$this->iteratorUsedEvent], $this->callVerifierFactory),
            $this->subject->once()->used()
        );

        $this->setUpWith([$this->iteratorCall]);

        $this->assertEquals(
            new EventSequence([$this->iteratorUsedEvent], $this->callVerifierFactory),
            $this->subject->always()->used()
        );
    }

    public function testUsedFailureNonIterables()
    {
        $this->setUpWith($this->nonIterableCalls);

        $this->expectException(AssertionException::class);
        $this->subject->used();
    }

    public function testUsedFailureNeverUsed()
    {
        $this->iteratorCall = $this->callFactory->create(
            $this->iterableCalledEvent,
            $this->returnedIterableEvent
        );
        $this->setUpWith([$this->iteratorCall]);

        $this->expectException(AssertionException::class);
        $this->subject->used();
    }

    public function testUsedFailureAlways()
    {
        $this->setUpWith($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->always()->used();
    }

    public function testUsedFailureNever()
    {
        $this->setUpWith($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->never()->used();
    }

    public function testCheckProduced()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkProduced());
        $this->assertFalse((bool) $this->subject->checkProduced('n'));
        $this->assertFalse((bool) $this->subject->checkProduced('m', 'n'));
        $this->assertFalse((bool) $this->subject->times(1)->checkProduced());
        $this->assertFalse((bool) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((bool) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((bool) $this->subject->checkProduced('m'));
        $this->assertFalse((bool) $this->subject->checkProduced('m', 'o'));

        $this->setUpWith($this->nonIterableCalls);

        $this->assertFalse((bool) $this->subject->checkProduced());
        $this->assertFalse((bool) $this->subject->checkProduced('n'));
        $this->assertFalse((bool) $this->subject->checkProduced('m', 'n'));
        $this->assertFalse((bool) $this->subject->times(1)->checkProduced());
        $this->assertFalse((bool) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((bool) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((bool) $this->subject->checkProduced('m'));
        $this->assertFalse((bool) $this->subject->checkProduced('m', 'o'));

        $this->setUpWith($this->calls);

        $this->assertTrue((bool) $this->subject->checkProduced());
        $this->assertTrue((bool) $this->subject->checkProduced('n'));
        $this->assertTrue((bool) $this->subject->checkProduced('m', 'n'));
        $this->assertTrue((bool) $this->subject->times(1)->checkProduced());
        $this->assertTrue((bool) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((bool) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((bool) $this->subject->checkProduced('m'));
        $this->assertFalse((bool) $this->subject->checkProduced('m', 'o'));
        $this->assertFalse((bool) $this->subject->always()->checkProduced());

        $this->setUpWith([$this->iteratorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkProduced());
    }

    public function testProduced()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence(
                [$this->iteratorEventA, $this->iteratorEventC, $this->iteratorEventE, $this->iteratorEventG],
                $this->callVerifierFactory
            ),
            $this->subject->produced()
        );
        $this->assertEquals(
            new EventSequence([$this->iteratorEventA], $this->callVerifierFactory),
            $this->subject->produced('n')
        );
        $this->assertEquals(
            new EventSequence([$this->iteratorEventA], $this->callVerifierFactory),
            $this->subject->produced('m', 'n')
        );
        $this->assertEquals(
            new EventSequence(
                [$this->iteratorEventA, $this->iteratorEventC, $this->iteratorEventE, $this->iteratorEventG],
                $this->callVerifierFactory
            ),
            $this->subject->times(1)->produced()
        );
        $this->assertEquals(
            new EventSequence([$this->iteratorEventA], $this->callVerifierFactory),
            $this->subject->once()->produced('n')
        );
        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->produced('m')
        );

        $this->setUpWith([$this->iteratorCall]);

        $this->assertEquals(
            new EventSequence(
                [$this->iteratorEventA, $this->iteratorEventC, $this->iteratorEventE, $this->iteratorEventG],
                $this->callVerifierFactory
            ),
            $this->subject->always()->produced()
        );
    }

    public function testProducedFailureNoIterablesNoMatchers()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->produced();
    }

    public function testProducedFailureNoIterablesValueOnly()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x');
    }

    public function testProducedFailureNoIterablesKeyAndValue()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x');
    }

    public function testProducedFailureKeyValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureAlways()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->always()->produced('n');
    }

    public function testProducedFailureNever()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->never()->produced('n');
    }

    public function testCheckConsumed()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkConsumed());
        $this->assertFalse((bool) $this->subject->times(1)->checkConsumed());
        $this->assertFalse((bool) $this->subject->once()->checkConsumed());
        $this->assertTrue((bool) $this->subject->never()->checkConsumed());

        $this->setUpWith($this->nonIterableCalls);

        $this->assertFalse((bool) $this->subject->checkConsumed());
        $this->assertFalse((bool) $this->subject->times(1)->checkConsumed());
        $this->assertFalse((bool) $this->subject->once()->checkConsumed());
        $this->assertTrue((bool) $this->subject->never()->checkConsumed());

        $this->setUpWith($this->calls);

        $this->assertTrue((bool) $this->subject->checkConsumed());
        $this->assertTrue((bool) $this->subject->times(1)->checkConsumed());
        $this->assertTrue((bool) $this->subject->once()->checkConsumed());
        $this->assertFalse((bool) $this->subject->never()->checkConsumed());
        $this->assertFalse((bool) $this->subject->always()->checkConsumed());

        $this->setUpWith([$this->iteratorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkConsumed());
    }

    public function testConsumed()
    {
        $this->setUpWith([]);

        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->consumed()
        );

        $this->setUpWith($this->nonIterableCalls);

        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->consumed()
        );

        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence([$this->iterableEndEvent], $this->callVerifierFactory),
            $this->subject->consumed()
        );
        $this->assertEquals(
            new EventSequence([$this->iterableEndEvent], $this->callVerifierFactory),
            $this->subject->times(1)->consumed()
        );
        $this->assertEquals(
            new EventSequence([$this->iterableEndEvent], $this->callVerifierFactory),
            $this->subject->once()->consumed()
        );

        $this->setUpWith([$this->iteratorCall]);

        $this->assertEquals(
            new EventSequence([$this->iterableEndEvent], $this->callVerifierFactory),
            $this->subject->always()->consumed()
        );
    }

    public function testConsumedFailureNonIterables()
    {
        $this->setUpWith($this->nonIterableCalls);
        $this->expectException(AssertionException::class);
        $this->subject->consumed();
    }

    public function testConsumedFailureNeverConsumed()
    {
        $this->iteratorCall = $this->callFactory->create(
            $this->iterableCalledEvent,
            $this->returnedIterableEvent,
            $this->iteratorEvents
        );
        $this->setUpWith([$this->iteratorCall]);
        $this->expectException(AssertionException::class);
        $this->subject->consumed();
    }

    public function testConsumedFailureAlways()
    {
        $this->setUpWith($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->always()->consumed();
    }

    public function testConsumedFailureNever()
    {
        $this->setUpWith($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->never()->consumed();
    }

    public function testCardinalityMethods()
    {
        $this->setUpWith([]);
        $this->subject->never();

        $this->assertEquals(new Cardinality(0, 0), $this->subject->never()->cardinality());
        $this->assertEquals(new Cardinality(1, 1), $this->subject->once()->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->times(2)->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->twice()->cardinality());
        $this->assertEquals(new Cardinality(3, 3), $this->subject->thrice()->cardinality());
        $this->assertEquals(new Cardinality(3), $this->subject->atLeast(3)->cardinality());
        $this->assertEquals(new Cardinality(0, 4), $this->subject->atMost(4)->cardinality());
        $this->assertEquals(new Cardinality(5, 6), $this->subject->between(5, 6)->cardinality());
        $this->assertEquals(new Cardinality(5, 6, true), $this->subject->between(5, 6)->always()->cardinality());
    }
}
