<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use AllowDynamicProperties;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyMap;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Error;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[AllowDynamicProperties]
class GeneratorVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        $this->callFactory = new TestCallFactory();
        $this->eventFactory = $this->callFactory->eventFactory();
        $this->anyMatcher = new AnyMatcher();
        $this->wildcardAnyMatcher = WildcardMatcher::instance();
        $this->idSequencer = new Sequencer();
        $this->invocableInspector = InvocableInspector::instance();
        $this->featureDetector = new FeatureDetector();
        $this->generatorSpyMap = GeneratorSpyMap::instance();
        $this->exporter = new InlineExporter(
            1,
            $this->idSequencer,
            $this->generatorSpyMap,
            $this->invocableInspector
        );
        $this->matcherFactory = new MatcherFactory($this->anyMatcher, $this->wildcardAnyMatcher, $this->exporter);

        $this->receivedExceptionA = new RuntimeException('Consequences will never be the same.');
        $this->receivedExceptionB = new RuntimeException('Because I backtraced it.');
        $this->generatorCalledEvent = $this->eventFactory->createCalled();
        $this->generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $this->generatorUsedEvent = $this->eventFactory->createUsed();
        $this->generatorEventA = $this->eventFactory->createProduced('m', 'n');
        $this->generatorEventB = $this->eventFactory->createReceived('o');
        $this->generatorEventC = $this->eventFactory->createProduced('p', 'q');
        $this->generatorEventD = $this->eventFactory->createReceivedException($this->receivedExceptionA);
        $this->generatorEventE = $this->eventFactory->createProduced('r', 's');
        $this->generatorEventF = $this->eventFactory->createReceived('t');
        $this->generatorEventG = $this->eventFactory->createProduced('u', 'v');
        $this->generatorEventH = $this->eventFactory->createReceivedException($this->receivedExceptionB);
        $this->generatorEvents = [
            $this->generatorUsedEvent,
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
        ];
        $this->generatorEndEvent = $this->eventFactory->createReturned('w');
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );

        $this->returnedEvent = $this->eventFactory->createReturned(null);
        $this->calls = [
            $this->callFactory->create(
                null,
                $this->returnedEvent,
                [],
                $this->returnedEvent
            ),
            $this->generatorCall,
        ];

        $this->callVerifierFactory = CallVerifierFactory::instance();
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
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments->all());
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->eventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], $this->arguments),
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
            $this->eventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], $this->arguments),
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

        $this->nonGeneratorCalls = [$this->callA, $this->callB];

        $this->typicalCallsPlusGeneratorCall = $this->typicalCalls;
        $this->typicalCallsPlusGeneratorCall[] = $this->generatorCall;

        $this->generatorThrewEvent = $this->eventFactory->createThrew($this->exceptionA);
        $this->generatorThrowCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorThrewEvent
        );

        $this->callsWithThrow = [
            $this->calls[0],
            $this->generatorThrowCall,
        ];
    }

    private function setUpWith($calls)
    {
        $this->spyFactory = SpyFactory::instance();
        $this->spy = $this->spyFactory->create('implode')->setLabel('label');
        $this->spy->setCalls($calls);
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->matcherVerifier = MatcherVerifier::instance();
        $this->differenceEngine = new DifferenceEngine($this->featureDetector);
        $this->differenceEngine->setUseColor(false);
        $this->assertionRenderer = new AssertionRenderer(
            $this->matcherVerifier,
            $this->exporter,
            $this->differenceEngine,
            $this->featureDetector
        );
        $this->assertionRenderer->setUseColor(false);
        $this->subject = new GeneratorVerifier(
            $this->spy,
            $calls,
            $this->matcherFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
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

        $this->setUpWith($this->nonGeneratorCalls);

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

        $this->setUpWith([$this->generatorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkUsed());
    }

    public function testUsed()
    {
        $this->setUpWith([]);

        $this->assertEquals(new EventSequence([], $this->callVerifierFactory), $this->subject->never()->used());

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertEquals(new EventSequence([], $this->callVerifierFactory), $this->subject->never()->used());

        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence([$this->generatorUsedEvent], $this->callVerifierFactory),
            $this->subject->used()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorUsedEvent], $this->callVerifierFactory),
            $this->subject->times(1)->used()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorUsedEvent], $this->callVerifierFactory),
            $this->subject->once()->used()
        );

        $this->setUpWith([$this->generatorCall]);

        $this->assertEquals(
            new EventSequence([$this->generatorUsedEvent], $this->callVerifierFactory),
            $this->subject->always()->used()
        );
    }

    public function testUsedFailureNonIterables()
    {
        $this->setUpWith($this->nonGeneratorCalls);
        $this->expectException(AssertionException::class);
        $this->subject->used();
    }

    public function testUsedFailureNeverUsed()
    {
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent
        );
        $this->setUpWith([$this->generatorCall]);
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

        $this->setUpWith($this->nonGeneratorCalls);

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

        $this->setUpWith([$this->generatorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkProduced());
    }

    public function testProduced()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence(
                [$this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG],
                $this->callVerifierFactory
            ),
            $this->subject->produced()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventA], $this->callVerifierFactory),
            $this->subject->produced('n')
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventA], $this->callVerifierFactory),
            $this->subject->produced('m', 'n')
        );
        $this->assertEquals(
            new EventSequence(
                [$this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG],
                $this->callVerifierFactory
            ),
            $this->subject->times(1)->produced()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventA], $this->callVerifierFactory),
            $this->subject->once()->produced('n')
        );
        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->produced('m')
        );

        $this->setUpWith([$this->generatorCall]);

        $this->assertEquals(
            new EventSequence(
                [$this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG],
                $this->callVerifierFactory
            ),
            $this->subject->always()->produced()
        );
    }

    public function testProducedFailureNoGeneratorsNoMatchers()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->produced();
    }

    public function testProducedFailureNoGeneratorsValueOnly()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x');
    }

    public function testProducedFailureNoGeneratorsKeyAndValue()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x');
    }

    public function testProducedFailureKeyValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureAlways()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->always()->produced('n');
    }

    public function testProducedFailureNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->never()->produced('n');
    }

    public function testCheckReceived()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkReceived());
        $this->assertFalse((bool) $this->subject->checkReceived('o'));
        $this->assertFalse((bool) $this->subject->times(1)->checkReceived());
        $this->assertFalse((bool) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((bool) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((bool) $this->subject->always()->checkReceived());
        $this->assertFalse((bool) $this->subject->checkReceived('x'));

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertFalse((bool) $this->subject->checkReceived());
        $this->assertFalse((bool) $this->subject->checkReceived('o'));
        $this->assertFalse((bool) $this->subject->times(1)->checkReceived());
        $this->assertFalse((bool) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((bool) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((bool) $this->subject->always()->checkReceived());
        $this->assertFalse((bool) $this->subject->checkReceived('x'));

        $this->setUpWith($this->calls);

        $this->assertTrue((bool) $this->subject->checkReceived());
        $this->assertTrue((bool) $this->subject->checkReceived('o'));
        $this->assertTrue((bool) $this->subject->times(1)->checkReceived());
        $this->assertTrue((bool) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((bool) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((bool) $this->subject->always()->checkReceived());
        $this->assertFalse((bool) $this->subject->checkReceived('x'));
    }

    public function testReceived()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);

        $this->assertEquals(
            new EventSequence([$this->generatorEventB, $this->generatorEventF], $this->callVerifierFactory),
            $this->subject->received()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventB], $this->callVerifierFactory),
            $this->subject->received('o')
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventB, $this->generatorEventF], $this->callVerifierFactory),
            $this->subject->times(1)->received()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventB], $this->callVerifierFactory),
            $this->subject->once()->received('o')
        );
        $this->assertEquals(new EventSequence([], $this->callVerifierFactory), $this->subject->never()->received('x'));
    }

    public function testReceivedFailureNoGeneratorsNoMatcher()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->received();
    }

    public function testReceivedFailureNoGeneratorsWithMatcher()
    {
        $this->setUpWith($this->typicalCalls);
        $this->expectException(AssertionException::class);
        $this->subject->received('x');
    }

    public function testReceivedFailureMatcherMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->received('x');
    }

    public function testCheckReceivedException()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkReceivedException());
        $this->assertFalse((bool) $this->subject->checkReceivedException(Exception::class));
        $this->assertFalse((bool) $this->subject->checkReceivedException(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse((bool) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertFalse(
            (bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertFalse((bool) $this->subject->checkReceivedException(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((bool) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo(null)));
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException());
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException(Exception::class));
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException(RuntimeException::class));
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue(
            (bool) $this->subject->never()
                ->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertFalse((bool) $this->subject->checkReceivedException());
        $this->assertFalse((bool) $this->subject->checkReceivedException(Exception::class));
        $this->assertFalse((bool) $this->subject->checkReceivedException(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse((bool) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertFalse(
            (bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertFalse((bool) $this->subject->checkReceivedException(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((bool) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo(null)));
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException());
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException(Exception::class));
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException(RuntimeException::class));
        $this->assertTrue((bool) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue(
            (bool) $this->subject->never()
                ->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );

        $this->setUpWith($this->calls);

        $this->assertTrue((bool) $this->subject->checkReceivedException());
        $this->assertTrue((bool) $this->subject->checkReceivedException(Exception::class));
        $this->assertTrue((bool) $this->subject->checkReceivedException(RuntimeException::class));
        $this->assertTrue((bool) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue((bool) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertTrue(
            (bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertFalse((bool) $this->subject->checkReceivedException(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((bool) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((bool) $this->subject->checkReceivedException($this->matcherFactory->equalTo(null)));
        $this->assertFalse((bool) $this->subject->never()->checkReceivedException());
        $this->assertFalse((bool) $this->subject->never()->checkReceivedException(Exception::class));
        $this->assertFalse((bool) $this->subject->never()->checkReceivedException(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse(
            (bool) $this->subject->never()
                ->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
    }

    public function testReceivedException()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);

        $this->assertEquals(
            new EventSequence([$this->generatorEventD, $this->generatorEventH], $this->callVerifierFactory),
            $this->subject->receivedException()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventD, $this->generatorEventH], $this->callVerifierFactory),
            $this->subject->receivedException(Exception::class)
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventD, $this->generatorEventH], $this->callVerifierFactory),
            $this->subject->receivedException(RuntimeException::class)
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventD], $this->callVerifierFactory),
            $this->subject->receivedException($this->receivedExceptionA)
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventH], $this->callVerifierFactory),
            $this->subject->receivedException($this->receivedExceptionB)
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEventD], $this->callVerifierFactory),
            $this->subject->receivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->receivedException(InvalidArgumentException::class)
        );
    }

    public function testReceivedExceptionWithInstanceHandle()
    {
        $builder = MockBuilderFactory::instance()->create(RuntimeException::class);
        $exception = $builder->get();
        $events = [
            $this->generatorUsedEvent,
            $this->eventFactory->createReceivedException($exception),
        ];
        $call = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $events,
            $this->generatorEndEvent
        );
        $this->setUpWith([$call]);
        $handle = Phony::on($exception);

        $this->assertTrue((bool) $this->subject->receivedException($handle));
        $this->assertTrue((bool) $this->subject->checkReceivedException($handle));
    }

    public function testReceivedExceptionFailureExpectingNeverAny()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->never()->receivedException();
    }

    public function testReceivedExceptionFailureExpectingAlwaysAny()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->always()->receivedException();
    }

    public function testReceivedExceptionFailureTypeMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->receivedException(InvalidArgumentException::class);
    }

    public function testReceivedExceptionFailureTypeNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->never()->receivedException(RuntimeException::class);
    }

    public function testReceivedExceptionFailureExpectingTypeNoneReceived()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->receivedException(InvalidArgumentException::class);
    }

    public function testReceivedExceptionFailureExceptionMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureExceptionNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->never()->receivedException($this->receivedExceptionA);
    }

    public function testReceivedExceptionFailureExpectingExceptionNoneReceived()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureMatcherMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->receivedException($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testReceivedExceptionFailureMatcherNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->expectException(AssertionException::class);
        $this->subject->never()->receivedException($this->matcherFactory->equalTo($this->receivedExceptionA));
    }

    public function testReceivedExceptionFailureInvalidInput()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against 111.');
        $this->subject->receivedException(111);
    }

    public function testReceivedExceptionFailureInvalidInputObject()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against #0{}.');
        $this->subject->receivedException((object) []);
    }

    public function testCheckConsumed()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkConsumed());
        $this->assertFalse((bool) $this->subject->times(1)->checkConsumed());
        $this->assertFalse((bool) $this->subject->once()->checkConsumed());
        $this->assertTrue((bool) $this->subject->never()->checkConsumed());

        $this->setUpWith($this->nonGeneratorCalls);

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

        $this->setUpWith([$this->generatorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkConsumed());
    }

    public function testConsumed()
    {
        $this->setUpWith([]);

        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->consumed()
        );

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->consumed()
        );

        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->consumed()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->times(1)->consumed()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->once()->consumed()
        );

        $this->setUpWith([$this->generatorCall]);

        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->always()->consumed()
        );

        $this->generatorEndEvent = $this->eventFactory->createThrew($this->exceptionA);
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );
        $this->setUpWith([$this->generatorCall]);

        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->always()->consumed()
        );
    }

    public function testConsumedFailureNonIterables()
    {
        $this->setUpWith($this->nonGeneratorCalls);
        $this->expectException(AssertionException::class);
        $this->subject->consumed();
    }

    public function testConsumedFailureNeverConsumed()
    {
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents
        );
        $this->setUpWith([$this->generatorCall]);
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

    public function testCheckReturned()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkReturned());
        $this->assertFalse((bool) $this->subject->checkReturned(null));
        $this->assertFalse((bool) $this->subject->checkReturned($this->returnValueA));
        $this->assertFalse((bool) $this->subject->checkReturned($this->returnValueB));
        $this->assertFalse(
            (bool) $this->subject->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((bool) $this->subject->checkReturned('z'));

        $this->setUpWith($this->calls);

        $this->assertTrue((bool) $this->subject->checkReturned());
        $this->assertFalse((bool) $this->subject->checkReturned(null));
        $this->assertTrue((bool) $this->subject->checkReturned('w'));
        $this->assertTrue((bool) $this->subject->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((bool) $this->subject->checkReturned('z'));
    }

    public function testReturned()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->returned()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->returned('w')
        );
        $this->assertEquals(
            new EventSequence([$this->generatorEndEvent], $this->callVerifierFactory),
            $this->subject->returned($this->matcherFactory->equalTo('w'))
        );
    }

    public function testReturnedFailure()
    {
        $this->setUpWith($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->returned('z');
    }

    public function testReturnedFailureWithoutMatcher()
    {
        $this->setUpWith($this->nonGeneratorCalls);

        $this->expectException(AssertionException::class);
        $this->subject->returned();
    }

    public function testCheckAlwaysReturned()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->always()->checkReturned());
        $this->assertFalse((bool) $this->subject->always()->checkReturned(null));
        $this->assertFalse((bool) $this->subject->always()->checkReturned('w'));
        $this->assertFalse((bool) $this->subject->always()->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((bool) $this->subject->always()->checkReturned('z'));

        $this->setUpWith($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkReturned());
        $this->assertFalse((bool) $this->subject->always()->checkReturned(null));
        $this->assertFalse((bool) $this->subject->always()->checkReturned('w'));
        $this->assertFalse((bool) $this->subject->always()->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((bool) $this->subject->always()->checkReturned('z'));

        $this->setUpWith([$this->generatorCall, $this->generatorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkReturned());
        $this->assertTrue((bool) $this->subject->always()->checkReturned('w'));
        $this->assertTrue((bool) $this->subject->always()->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((bool) $this->subject->always()->checkReturned(null));
        $this->assertFalse((bool) $this->subject->always()->checkReturned('y'));
    }

    public function testAlwaysReturned()
    {
        $this->setUpWith([$this->generatorCall, $this->generatorCall]);
        $expected =
            new EventSequence([$this->generatorEndEvent, $this->generatorEndEvent], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->always()->returned());
        $this->assertEquals($expected, $this->subject->always()->returned('w'));
        $this->assertEquals($expected, $this->subject->always()->returned($this->matcherFactory->equalTo('w')));
    }

    public function testAlwaysReturnedFailure()
    {
        $this->setUpWith($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->always()->returned('w');
    }

    public function testAlwaysReturnedFailureWithNoMatcher()
    {
        $this->setUpWith($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->always()->returned();
    }

    public function testCheckThrew()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->checkThrew());
        $this->assertFalse((bool) $this->subject->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subject->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->checkThrew($this->exceptionA));
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((bool) $this->subject->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith($this->callsWithThrow);

        $this->assertTrue((bool) $this->subject->checkThrew());
        $this->assertTrue((bool) $this->subject->checkThrew(Exception::class));
        $this->assertTrue((bool) $this->subject->checkThrew(RuntimeException::class));
        $this->assertTrue((bool) $this->subject->checkThrew($this->exceptionA));
        $this->assertTrue((bool) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((bool) $this->subject->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against 111.');
        $this->subject->checkThrew(111);
    }

    public function testCheckThrewFailureInvalidInputObject()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against #0{}.');
        $this->subject->checkThrew((object) []);
    }

    public function testThrew()
    {
        $this->setUpWith($this->callsWithThrow);

        $this->assertEquals(
            new EventSequence([$this->generatorThrewEvent], $this->callVerifierFactory),
            $this->subject->threw()
        );
        $this->assertEquals(
            new EventSequence([$this->generatorThrewEvent], $this->callVerifierFactory),
            $this->subject->threw(Exception::class)
        );
        $this->assertEquals(
            new EventSequence([$this->generatorThrewEvent], $this->callVerifierFactory),
            $this->subject->threw(RuntimeException::class)
        );
        $this->assertEquals(
            new EventSequence([$this->generatorThrewEvent], $this->callVerifierFactory),
            $this->subject->threw($this->exceptionA)
        );
        $this->assertEquals(
            new EventSequence([$this->generatorThrewEvent], $this->callVerifierFactory),
            $this->subject->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testThrewWithEngineErrorException()
    {
        $this->exceptionA = new Error('You done goofed.');
        $this->generatorThrewEvent = $this->eventFactory->createThrew($this->exceptionA);
        $this->generatorThrowCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorThrewEvent
        );

        $this->callsWithThrow = [
            $this->calls[0],
            $this->generatorThrowCall,
        ];
        $this->setUpWith($this->callsWithThrow);

        $this->assertEquals(
            new EventSequence([$this->generatorThrewEvent], $this->callVerifierFactory),
            $this->subject->threw()
        );
    }

    public function testThrewWithInstanceHandle()
    {
        $builder = MockBuilderFactory::instance()->create(RuntimeException::class);
        $exception = $builder->get();
        $this->generatorThrewEvent = $this->eventFactory->createThrew($exception);
        $this->generatorThrowCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorThrewEvent
        );

        $this->callsWithThrow = [
            $this->calls[0],
            $this->generatorThrowCall,
        ];
        $this->setUpWith($this->callsWithThrow);
        $handle = Phony::on($exception);

        $this->assertTrue((bool) $this->subject->threw($handle));
        $this->assertTrue((bool) $this->subject->checkThrew($handle));
    }

    public function testThrewFailureExpectingAny()
    {
        $this->setUpWith($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->threw();
    }

    public function testThrewFailureExpectingType()
    {
        $this->setUpWith($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->threw(UndefinedCallException::class);
    }

    public function testThrewFailureExpectingException()
    {
        $this->setUpWith($this->callsWithThrow);
        $this->expectException(AssertionException::class);
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingMatcher()
    {
        $this->setUpWith($this->callsWithThrow);
        $this->expectException(AssertionException::class);
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against 111.');
        $this->subject->threw(111);
    }

    public function testThrewFailureInvalidInputObject()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against #0{}.');
        $this->subject->threw((object) []);
    }

    public function testCheckAlwaysThrew()
    {
        $this->setUpWith([]);

        $this->assertFalse((bool) $this->subject->always()->checkThrew());
        $this->assertFalse((bool) $this->subject->always()->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse(
            (bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkThrew());
        $this->assertFalse((bool) $this->subject->always()->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse(
            (bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith([$this->generatorThrowCall, $this->generatorThrowCall]);

        $this->assertTrue((bool) $this->subject->always()->checkThrew());
        $this->assertTrue((bool) $this->subject->always()->checkThrew(Exception::class));
        $this->assertTrue((bool) $this->subject->always()->checkThrew(RuntimeException::class));
        $this->assertTrue((bool) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertTrue(
            (bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testAlwaysThrew()
    {
        $this->setUpWith([$this->generatorThrowCall, $this->generatorThrowCall]);
        $expected = new EventSequence(
            [$this->generatorThrewEvent, $this->generatorThrewEvent],
            $this->callVerifierFactory
        );

        $this->assertEquals($expected, $this->subject->always()->threw());
        $this->assertEquals($expected, $this->subject->always()->threw(Exception::class));
        $this->assertEquals($expected, $this->subject->always()->threw(RuntimeException::class));
        $this->assertEquals($expected, $this->subject->always()->threw($this->exceptionA));
        $this->assertEquals(
            $expected,
            $this->subject->always()->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testAlwaysThrewFailureExpectingAny()
    {
        $this->setUpWith($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingType()
    {
        $this->setUpWith([$this->generatorThrowCall, $this->generatorThrowCall]);
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(UndefinedCallException::class);
    }

    public function testAlwaysThrewFailureExpectingException()
    {
        $this->setUpWith([$this->generatorThrowCall, $this->generatorThrowCall]);
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingMatcher()
    {
        $this->setUpWith([$this->generatorThrowCall, $this->generatorThrowCall]);
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
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
