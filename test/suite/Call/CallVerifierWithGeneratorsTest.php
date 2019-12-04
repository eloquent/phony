<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CallVerifierWithGeneratorsTest extends TestCase
{
    protected function setUp(): void
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callEventFactory->sequencer()->set(111);
        $this->thisValue = new TestClassA();
        $this->callback = [$this->thisValue, 'testClassAMethodA'];
        $this->arguments = new Arguments(['a', 'b', 'c']);
        $this->returnValue = 'abc';
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->call = $this->callFactory->create($this->calledEvent, $this->returnedEvent, null, $this->returnedEvent);

        $this->arraySequencer = new Sequencer();
        $this->objectSequencer = new Sequencer();
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = FeatureDetector::instance();
        $this->exporter = new InlineExporter(
            1,
            $this->arraySequencer,
            $this->objectSequencer,
            $this->invocableInspector,
            $this->featureDetector
        );
        $this->matcherFactory =
            new MatcherFactory(AnyMatcher::instance(), WildcardMatcher::instance(), $this->exporter);
        $this->matcherVerifier = new MatcherVerifier();
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->iterableVerifierFactory = IterableVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->differenceEngine = new DifferenceEngine($this->featureDetector);
        $this->differenceEngine->setUseColor(false);
        $this->assertionRenderer = new AssertionRenderer(
            $this->matcherVerifier,
            $this->exporter,
            $this->differenceEngine,
            $this->featureDetector
        );
        $this->assertionRenderer->setUseColor(false);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->generatorVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);
        $this->iterableVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);

        $this->duration = $this->returnedEvent->time() - $this->calledEvent->time();
        $this->argumentCount = count($this->arguments);
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments->all());
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->events = [$this->calledEvent, $this->returnedEvent];

        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException =
            $this->callFactory->create($this->calledEvent, $this->threwEvent, null, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoArguments = $this->callFactory
            ->create($this->calledEventWithNoArguments, $this->returnedEvent);
        $this->subjectWithNoArguments = new CallVerifier(
            $this->callWithNoArguments,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoResponse = $this->callFactory->create($this->calledEvent);
        $this->subjectWithNoResponse = new CallVerifier(
            $this->callWithNoResponse,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->callEventFactory->sequencer()->reset();
        $this->earlyCall = $this->callFactory->create();
        $this->callEventFactory->sequencer()->set(222);
        $this->lateCall = $this->callFactory->create();

        // additions for generators

        $this->receivedExceptionA = new RuntimeException('Consequences will never be the same.');
        $this->receivedExceptionB = new RuntimeException('Because I backtraced it.');
        $this->generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $this->generatorEventA = $this->callEventFactory->createProduced('m', 'n');
        $this->generatorEventB = $this->callEventFactory->createReceived('o');
        $this->generatorEventC = $this->callEventFactory->createProduced('p', 'q');
        $this->generatorEventD = $this->callEventFactory->createReceivedException($this->receivedExceptionA);
        $this->generatorEventE = $this->callEventFactory->createProduced('r', 's');
        $this->generatorEventF = $this->callEventFactory->createReceived('t');
        $this->generatorEventG = $this->callEventFactory->createProduced('u', 'v');
        $this->generatorEventH = $this->callEventFactory->createReceivedException($this->receivedExceptionB);
        $this->generatorEvents = [
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
        ];
        $this->generatorEndEvent = $this->callEventFactory->createReturned(null);
        $this->generatorCall = $this->callFactory->create(
            $this->calledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );
        $this->generatorCallEvents = [
            $this->calledEvent,
            $this->generatedEvent,
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
            $this->generatorEndEvent,
        ];
        $this->generatorSubject = new CallVerifier(
            $this->generatorCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
    }

    public function testProxyMethodsWithGeneratorEvents()
    {
        $this->assertSame($this->calledEvent, $this->generatorSubject->calledEvent());
        $this->assertSame($this->generatedEvent, $this->generatorSubject->responseEvent());
        $this->assertSame($this->generatorEvents, $this->generatorSubject->iterableEvents());
        $this->assertSame($this->generatorEndEvent, $this->generatorSubject->endEvent());
        $this->assertSame($this->generatorCallEvents, $this->generatorSubject->allEvents());
        $this->assertTrue($this->generatorSubject->hasResponded());
        $this->assertTrue($this->generatorSubject->isGenerator());
        $this->assertTrue($this->generatorSubject->hasCompleted());
        $this->assertSame($this->callback, $this->generatorSubject->callback());
        $this->assertSame($this->arguments, $this->generatorSubject->arguments());
        $this->assertInstanceOf(Generator::class, $this->generatorSubject->returnValue());
        $this->assertNull($this->generatorSubject->generatorReturnValue());
        $this->assertSame([null, null], $this->generatorSubject->generatorResponse());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->generatorSubject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->generatorSubject->time());
        $this->assertSame($this->generatedEvent->time(), $this->generatorSubject->responseTime());
        $this->assertSame($this->generatorEndEvent->time(), $this->generatorSubject->endTime());
    }

    public function testProxyMethodsWithGeneratorEventsWithThrowEnd()
    {
        $this->generatorCall = $this->callFactory->create(
            $this->calledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->threwEvent
        );
        $this->generatorSubject = new CallVerifier(
            $this->generatorCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->assertSame($this->exception, $this->generatorSubject->generatorException());
        $this->assertSame([$this->exception, null], $this->generatorSubject->generatorResponse());
    }

    public function testAddGeneratorEvent()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->callEventFactory->createProduced(null, null);
        $generatorEventB = $this->callEventFactory->createReceived(null);
        $generatorEvents = [$generatorEventA, $generatorEventB];
        $this->call = $this->callFactory->create($this->calledEvent, $generatedEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $this->subject->addIterableEvent($generatorEventA);
        $this->subject->addIterableEvent($generatorEventB);

        $this->assertSame($generatorEvents, $this->subject->iterableEvents());
    }

    public function testDurationMethodsWithGeneratorEvents()
    {
        $this->assertEquals(7, $this->generatorSubject->responseDuration());
        $this->assertNull($this->subjectWithNoResponse->duration());
    }

    public function testReturnedFailureWithGenerator()
    {
        $this->expectException(AssertionException::class);
        $this->generatorSubject->returned(null);
    }

    public function testThrewFailureWithGenerator()
    {
        $this->generatorCall = $this->callFactory->create(
            $this->calledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->threwEvent
        );
        $this->generatorSubject = new CallVerifier(
            $this->generatorCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->expectException(AssertionException::class);
        $this->generatorSubject->threw();
    }

    public function testCheckGenerated()
    {
        $this->assertTrue((bool) $this->generatorSubject->checkGenerated());
        $this->assertTrue((bool) $this->generatorSubject->once()->checkGenerated());
        $this->assertFalse((bool) $this->subject->checkGenerated());
        $this->assertTrue((bool) $this->subject->never()->checkGenerated());
        $this->assertFalse((bool) $this->subjectWithNoResponse->checkGenerated());
        $this->assertTrue((bool) $this->subjectWithNoResponse->never()->checkGenerated());
    }

    public function testGenerated()
    {
        $this->assertEquals(
            $this->generatorVerifierFactory->create($this->generatorCall, [$this->generatorCall]),
            $this->generatorSubject->generated()
        );
        $this->assertEquals(
            $this->generatorVerifierFactory->create($this->call, []),
            $this->subject->never()->generated()
        );
    }

    public function testGeneratedFailure()
    {
        $this->expectException(AssertionException::class);
        $this->subject->generated();
    }

    public function testGeneratedFailureNever()
    {
        $this->expectException(AssertionException::class);
        $this->generatorSubject->never()->generated();
    }

    public function testGeneratedFailureWithException()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->generated();
    }

    public function testGeneratedFailureNeverResponded()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithNoResponse->generated();
    }
}
