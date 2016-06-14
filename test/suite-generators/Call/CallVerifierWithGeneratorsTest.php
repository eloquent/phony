<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventSequence;
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
use Eloquent\Phony\Verification\TraversableVerifierFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class CallVerifierWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callEventFactory->sequencer()->set(111);
        $this->thisValue = new TestClassA();
        $this->callback = array($this->thisValue, 'testClassAMethodA');
        $this->arguments = new Arguments(array('a', 'b', 'c'));
        $this->returnValue = 'abc';
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->call = $this->callFactory->create($this->calledEvent, $this->returnedEvent, null, $this->returnedEvent);

        $this->objectSequencer = new Sequencer();
        $this->exporter = new InlineExporter(1, $this->objectSequencer);
        $this->matcherFactory =
            new MatcherFactory(AnyMatcher::instance(), WildcardMatcher::instance(), $this->exporter);
        $this->matcherVerifier = new MatcherVerifier();
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->traversableVerifierFactory = TraversableVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = FeatureDetector::instance();
        $this->differenceEngine = new DifferenceEngine($this->featureDetector);
        $this->differenceEngine->setUseColor(false);
        $this->assertionRenderer = new AssertionRenderer(
            $this->invocableInspector,
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
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->generatorVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);
        $this->traversableVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);

        $this->duration = $this->returnedEvent->time() - $this->calledEvent->time();
        $this->argumentCount = count($this->arguments);
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments->all());
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->events = array($this->calledEvent, $this->returnedEvent);

        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException =
            $this->callFactory->create($this->calledEvent, $this->threwEvent, null, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoArguments = $this->callFactory
            ->create($this->calledEventWithNoArguments, $this->returnedEvent);
        $this->subjectWithNoArguments = new CallVerifier(
            $this->callWithNoArguments,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoResponse = $this->callFactory->create($this->calledEvent);
        $this->subjectWithNoResponse = new CallVerifier(
            $this->callWithNoResponse,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callEventFactory->sequencer()->reset();
        $this->earlyCall = $this->callFactory->create();
        $this->callEventFactory->sequencer()->set(222);
        $this->lateCall = $this->callFactory->create();

        $this->assertionResult = new EventSequence(array($this->call));
        $this->returnedAssertionResult = new EventSequence(array($this->call->responseEvent()));
        $this->threwAssertionResult = new EventSequence(array($this->callWithException->responseEvent()));
        $this->emptyAssertionResult = new EventSequence(array());

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
        $this->generatorEvents = array(
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
        );
        $this->generatorEndEvent = $this->callEventFactory->createReturned(null);
        $this->generatorCall = $this->callFactory->create(
            $this->calledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );
        $this->generatorCallEvents = array(
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
        );
        $this->generatorSubject = new CallVerifier(
            $this->generatorCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    public function testProxyMethodsWithGeneratorEvents()
    {
        $this->assertSame($this->calledEvent, $this->generatorSubject->calledEvent());
        $this->assertSame($this->generatedEvent, $this->generatorSubject->responseEvent());
        $this->assertSame($this->generatorEvents, $this->generatorSubject->traversableEvents());
        $this->assertSame($this->generatorEndEvent, $this->generatorSubject->endEvent());
        $this->assertSame($this->generatorCallEvents, $this->generatorSubject->allEvents());
        $this->assertTrue($this->generatorSubject->hasResponded());
        $this->assertTrue($this->generatorSubject->isGenerator());
        $this->assertTrue($this->generatorSubject->hasCompleted());
        $this->assertSame($this->callback, $this->generatorSubject->callback());
        $this->assertSame($this->arguments, $this->generatorSubject->arguments());
        $this->assertInstanceOf('Generator', $this->generatorSubject->returnValue());
        $this->assertNull($this->generatorSubject->generatorReturnValue());
        $this->assertSame(array(null, null), $this->generatorSubject->generatorResponse());
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
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->assertSame($this->exception, $this->generatorSubject->generatorException());
        $this->assertSame(array($this->exception, null), $this->generatorSubject->generatorResponse());
    }

    public function testAddGeneratorEvent()
    {
        $generatedEvent = $this->callEventFactory->createReturned(GeneratorFactory::createEmpty());
        $generatorEventA = $this->callEventFactory->createProduced(null, null);
        $generatorEventB = $this->callEventFactory->createReceived(null);
        $generatorEvents = array($generatorEventA, $generatorEventB);
        $this->call = $this->callFactory->create($this->calledEvent, $generatedEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $this->subject->addTraversableEvent($generatorEventA);
        $this->subject->addTraversableEvent($generatorEventB);

        $this->assertSame($generatorEvents, $this->subject->traversableEvents());
    }

    public function testDurationMethodsWithGeneratorEvents()
    {
        $this->assertEquals(7, $this->generatorSubject->responseDuration());
        $this->assertNull($this->subjectWithNoResponse->duration());
    }

    public function testReturnedFailureWithGenerator()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
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
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->generatorSubject->threw();
    }

    public function testCheckGenerated()
    {
        $this->assertTrue((boolean) $this->generatorSubject->checkGenerated());
        $this->assertTrue((boolean) $this->generatorSubject->once()->checkGenerated());
        $this->assertFalse((boolean) $this->subject->checkGenerated());
        $this->assertTrue((boolean) $this->subject->never()->checkGenerated());
        $this->assertFalse((boolean) $this->subjectWithNoResponse->checkGenerated());
        $this->assertTrue((boolean) $this->subjectWithNoResponse->never()->checkGenerated());
    }

    public function testGenerated()
    {
        $this->assertEquals(
            $this->generatorVerifierFactory->create($this->generatorCall, array($this->generatorCall)),
            $this->generatorSubject->generated()
        );
        $this->assertEquals(
            $this->generatorVerifierFactory->create($this->call, array()),
            $this->subject->never()->generated()
        );
    }

    public function testGeneratedFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->generated();
    }

    public function testGeneratedFailureNever()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->generatorSubject->never()->generated();
    }

    public function testGeneratedFailureWithException()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subjectWithException->generated();
    }

    public function testGeneratedFailureNeverResponded()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subjectWithNoResponse->generated();
    }
}
