<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventSequence;
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
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Error;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class GeneratorVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->eventFactory = $this->callFactory->eventFactory();
        $this->anyMatcher = new AnyMatcher();
        $this->wildcardAnyMatcher = WildcardMatcher::instance();
        $this->objectSequencer = new Sequencer();
        $this->invocableInspector = InvocableInspector::instance();
        $this->exporter = new InlineExporter(1, $this->objectSequencer, $this->invocableInspector);
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
        $this->generatorEvents = array(
            $this->generatorUsedEvent,
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
        );
        $this->generatorEndEvent = $this->eventFactory->createReturned('w');
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );

        $this->returnedEvent = $this->eventFactory->createReturned(null);
        $this->calls = array(
            $this->callFactory->create(
                null,
                $this->returnedEvent,
                array(),
                $this->returnedEvent
            ),
            $this->generatorCall,
        );

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->wrappedCalls = array(
            $this->callVerifierFactory->fromCall($this->calls[0]),
            $this->callVerifierFactory->fromCall($this->calls[1]),
        );

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
            $this->eventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
            ($responseEvent = $this->eventFactory->createReturned($this->returnValueA)),
            null,
            $responseEvent
        );
        $this->callB = $this->callFactory->create(
            $this->eventFactory->createCalled(array($this->thisValueB, 'testClassAMethodA')),
            ($responseEvent = $this->eventFactory->createReturned($this->returnValueB)),
            null,
            $responseEvent
        );
        $this->callC = $this->callFactory->create(
            $this->eventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
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
        $this->typicalCalls = array($this->callA, $this->callB, $this->callC, $this->callD, $this->callE);

        $this->nonGeneratorCalls = array($this->callA, $this->callB);

        $this->typicalCallsPlusGeneratorCall = $this->typicalCalls;
        $this->typicalCallsPlusGeneratorCall[] = $this->generatorCall;

        $this->generatorThrewEvent = $this->eventFactory->createThrew($this->exceptionA);
        $this->generatorThrowCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorThrewEvent
        );

        $this->callsWithThrow = array(
            $this->calls[0],
            $this->generatorThrowCall,
        );

        $this->featureDetector = new FeatureDetector();
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
        $this->setUpWith(array());

        $this->assertEquals(new Cardinality(1, null), $this->subject->cardinality());
    }

    public function testHasEvents()
    {
        $this->setUpWith(array());

        $this->assertFalse($this->subject->hasEvents());

        $this->setUpWith(array($this->callA));

        $this->assertTrue($this->subject->hasEvents());
    }

    public function testHasCalls()
    {
        $this->setUpWith(array());

        $this->assertFalse($this->subject->hasCalls());

        $this->setUpWith(array($this->callA));

        $this->assertTrue($this->subject->hasCalls());
    }

    public function testEventCount()
    {
        $this->setUpWith(array());

        $this->assertSame(0, $this->subject->eventCount());

        $this->setUpWith(array($this->callA));

        $this->assertSame(1, $this->subject->eventCount());
    }

    public function testCallCount()
    {
        $this->setUpWith(array());

        $this->assertSame(0, $this->subject->callCount());
        $this->assertSame(0, count($this->subject));

        $this->setUpWith(array($this->callA));

        $this->assertSame(1, $this->subject->callCount());
        $this->assertSame(1, count($this->subject));
    }

    public function testAllEvents()
    {
        $this->setUpWith(array());

        $this->assertSame(array(), $this->subject->allEvents());

        $this->setUpWith(array($this->callA));

        $this->assertSame(array($this->callA), $this->subject->allEvents());
    }

    public function testAllCalls()
    {
        $this->setUpWith(array());

        $this->assertSame(array(), $this->subject->allCalls());

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
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->firstEvent();
    }

    public function testLastEvent()
    {
        $this->setUpWith($this->calls);

        $this->assertSame($this->calls[1], $this->subject->lastEvent());
    }

    public function testLastEventFailureUndefined()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->lastEvent();
    }

    public function testEventAt()
    {
        $this->setUpWith(array($this->callA));

        $this->assertSame($this->callA, $this->subject->eventAt());
        $this->assertSame($this->callA, $this->subject->eventAt(0));
        $this->assertSame($this->callA, $this->subject->eventAt(-1));
    }

    public function testEventAtFailure()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->eventAt();
    }

    public function testFirstCall()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals($this->wrappedCalls[0], $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals($this->wrappedCalls[1], $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
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
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->callAt(0);
    }

    public function testCheckUsed()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkUsed());
        $this->assertFalse((boolean) $this->subject->times(1)->checkUsed());
        $this->assertFalse((boolean) $this->subject->once()->checkUsed());
        $this->assertTrue((boolean) $this->subject->never()->checkUsed());

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertFalse((boolean) $this->subject->checkUsed());
        $this->assertFalse((boolean) $this->subject->times(1)->checkUsed());
        $this->assertFalse((boolean) $this->subject->once()->checkUsed());
        $this->assertTrue((boolean) $this->subject->never()->checkUsed());

        $this->setUpWith($this->calls);

        $this->assertTrue((boolean) $this->subject->checkUsed());
        $this->assertTrue((boolean) $this->subject->times(1)->checkUsed());
        $this->assertTrue((boolean) $this->subject->once()->checkUsed());
        $this->assertFalse((boolean) $this->subject->never()->checkUsed());
        $this->assertFalse((boolean) $this->subject->always()->checkUsed());

        $this->setUpWith(array($this->generatorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkUsed());
    }

    public function testUsed()
    {
        $this->setUpWith(array());

        $this->assertEquals(new EventSequence(array(), $this->callVerifierFactory), $this->subject->never()->used());

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertEquals(new EventSequence(array(), $this->callVerifierFactory), $this->subject->never()->used());

        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence(array($this->generatorUsedEvent), $this->callVerifierFactory),
            $this->subject->used()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorUsedEvent), $this->callVerifierFactory),
            $this->subject->times(1)->used()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorUsedEvent), $this->callVerifierFactory),
            $this->subject->once()->used()
        );

        $this->setUpWith(array($this->generatorCall));

        $this->assertEquals(
            new EventSequence(array($this->generatorUsedEvent), $this->callVerifierFactory),
            $this->subject->always()->used()
        );
    }

    public function testUsedFailureNonIterables()
    {
        $this->setUpWith($this->nonGeneratorCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->used();
    }

    public function testUsedFailureNeverUsed()
    {
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent
        );
        $this->setUpWith(array($this->generatorCall));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->used();
    }

    public function testUsedFailureAlways()
    {
        $this->setUpWith($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->used();
    }

    public function testUsedFailureNever()
    {
        $this->setUpWith($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->used();
    }

    public function testCheckProduced()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkProduced());
        $this->assertFalse((boolean) $this->subject->checkProduced('n'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'n'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkProduced());
        $this->assertFalse((boolean) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'o'));

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertFalse((boolean) $this->subject->checkProduced());
        $this->assertFalse((boolean) $this->subject->checkProduced('n'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'n'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkProduced());
        $this->assertFalse((boolean) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'o'));

        $this->setUpWith($this->calls);

        $this->assertTrue((boolean) $this->subject->checkProduced());
        $this->assertTrue((boolean) $this->subject->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->checkProduced('m', 'n'));
        $this->assertTrue((boolean) $this->subject->times(1)->checkProduced());
        $this->assertTrue((boolean) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'o'));
        $this->assertFalse((boolean) $this->subject->always()->checkProduced());

        $this->setUpWith(array($this->generatorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkProduced());
    }

    public function testProduced()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG),
                $this->callVerifierFactory
            ),
            $this->subject->produced()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventA), $this->callVerifierFactory),
            $this->subject->produced('n')
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventA), $this->callVerifierFactory),
            $this->subject->produced('m', 'n')
        );
        $this->assertEquals(
            new EventSequence(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG),
                $this->callVerifierFactory
            ),
            $this->subject->times(1)->produced()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventA), $this->callVerifierFactory),
            $this->subject->once()->produced('n')
        );
        $this->assertEquals(
            new EventSequence(array(), $this->callVerifierFactory),
            $this->subject->never()->produced('m')
        );

        $this->setUpWith(array($this->generatorCall));

        $this->assertEquals(
            new EventSequence(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG),
                $this->callVerifierFactory
            ),
            $this->subject->always()->produced()
        );
    }

    public function testProducedFailureNoGeneratorsNoMatchers()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced();
    }

    public function testProducedFailureNoGeneratorsValueOnly()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x');
    }

    public function testProducedFailureNoGeneratorsKeyAndValue()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x');
    }

    public function testProducedFailureKeyValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureAlways()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->produced('n');
    }

    public function testProducedFailureNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->produced('n');
    }

    public function testCheckReceived()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('o'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkReceived());
        $this->assertFalse((boolean) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('x'));

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertFalse((boolean) $this->subject->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('o'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkReceived());
        $this->assertFalse((boolean) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('x'));

        $this->setUpWith($this->calls);

        $this->assertTrue((boolean) $this->subject->checkReceived());
        $this->assertTrue((boolean) $this->subject->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->times(1)->checkReceived());
        $this->assertTrue((boolean) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('x'));
    }

    public function testReceived()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);

        $this->assertEquals(
            new EventSequence(array($this->generatorEventB, $this->generatorEventF), $this->callVerifierFactory),
            $this->subject->received()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventB), $this->callVerifierFactory),
            $this->subject->received('o')
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventB, $this->generatorEventF), $this->callVerifierFactory),
            $this->subject->times(1)->received()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventB), $this->callVerifierFactory),
            $this->subject->once()->received('o')
        );
        $this->assertEquals(new EventSequence(array(), $this->callVerifierFactory), $this->subject->never()->received('x'));
    }

    public function testReceivedFailureNoGeneratorsNoMatcher()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->received();
    }

    public function testReceivedFailureNoGeneratorsWithMatcher()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->received('x');
    }

    public function testReceivedFailureMatcherMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->received('x');
    }

    public function testCheckReceivedException()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkReceivedException());
        $this->assertFalse((boolean) $this->subject->checkReceivedException('Exception'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo(null)));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException());
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('Exception'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue(
            (boolean) $this->subject->never()
                ->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertFalse((boolean) $this->subject->checkReceivedException());
        $this->assertFalse((boolean) $this->subject->checkReceivedException('Exception'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo(null)));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException());
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('Exception'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue(
            (boolean) $this->subject->never()
                ->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );

        $this->setUpWith($this->calls);

        $this->assertTrue((boolean) $this->subject->checkReceivedException());
        $this->assertTrue((boolean) $this->subject->checkReceivedException('Exception'));
        $this->assertTrue((boolean) $this->subject->checkReceivedException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue((boolean) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertTrue(
            (boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->matcherFactory->equalTo(null)));
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException());
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException('Exception'));
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse(
            (boolean) $this->subject->never()
                ->checkReceivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
    }

    public function testReceivedException()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);

        $this->assertEquals(
            new EventSequence(array($this->generatorEventD, $this->generatorEventH), $this->callVerifierFactory),
            $this->subject->receivedException()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventD, $this->generatorEventH), $this->callVerifierFactory),
            $this->subject->receivedException('Exception')
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventD, $this->generatorEventH), $this->callVerifierFactory),
            $this->subject->receivedException('RuntimeException')
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventD), $this->callVerifierFactory),
            $this->subject->receivedException($this->receivedExceptionA)
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventH), $this->callVerifierFactory),
            $this->subject->receivedException($this->receivedExceptionB)
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEventD), $this->callVerifierFactory),
            $this->subject->receivedException($this->matcherFactory->equalTo($this->receivedExceptionA))
        );
        $this->assertEquals(
            new EventSequence(array(), $this->callVerifierFactory),
            $this->subject->never()->receivedException('InvalidArgumentException')
        );
    }

    public function testReceivedExceptionWithInstanceHandle()
    {
        $builder = MockBuilderFactory::instance()->create('RuntimeException');
        $exception = $builder->get();
        $events = array(
            $this->generatorUsedEvent,
            $this->eventFactory->createReceivedException($exception),
        );
        $call = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $events,
            $this->generatorEndEvent
        );
        $this->setUpWith(array($call));
        $handle = Phony::on($exception);

        $this->assertTrue((boolean) $this->subject->receivedException($handle));
        $this->assertTrue((boolean) $this->subject->checkReceivedException($handle));
    }

    public function testReceivedExceptionFailureExpectingNeverAny()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->receivedException();
    }

    public function testReceivedExceptionFailureExpectingAlwaysAny()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->receivedException();
    }

    public function testReceivedExceptionFailureTypeMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->receivedException('InvalidArgumentException');
    }

    public function testReceivedExceptionFailureTypeNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->receivedException('RuntimeException');
    }

    public function testReceivedExceptionFailureExpectingTypeNoneReceived()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->receivedException('InvalidArgumentException');
    }

    public function testReceivedExceptionFailureExceptionMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureExceptionNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->receivedException($this->receivedExceptionA);
    }

    public function testReceivedExceptionFailureExpectingExceptionNoneReceived()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureMatcherMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->receivedException($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testReceivedExceptionFailureMatcherNever()
    {
        $this->setUpWith($this->typicalCallsPlusGeneratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->receivedException($this->matcherFactory->equalTo($this->receivedExceptionA));
    }

    public function testReceivedExceptionFailureInvalidInput()
    {
        $this->setUpWith(array());

        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against 111.');
        $this->subject->receivedException(111);
    }

    public function testReceivedExceptionFailureInvalidInputObject()
    {
        $this->setUpWith(array());

        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against #0{}.');
        $this->subject->receivedException((object) array());
    }

    public function testCheckConsumed()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkConsumed());
        $this->assertFalse((boolean) $this->subject->times(1)->checkConsumed());
        $this->assertFalse((boolean) $this->subject->once()->checkConsumed());
        $this->assertTrue((boolean) $this->subject->never()->checkConsumed());

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertFalse((boolean) $this->subject->checkConsumed());
        $this->assertFalse((boolean) $this->subject->times(1)->checkConsumed());
        $this->assertFalse((boolean) $this->subject->once()->checkConsumed());
        $this->assertTrue((boolean) $this->subject->never()->checkConsumed());

        $this->setUpWith($this->calls);

        $this->assertTrue((boolean) $this->subject->checkConsumed());
        $this->assertTrue((boolean) $this->subject->times(1)->checkConsumed());
        $this->assertTrue((boolean) $this->subject->once()->checkConsumed());
        $this->assertFalse((boolean) $this->subject->never()->checkConsumed());
        $this->assertFalse((boolean) $this->subject->always()->checkConsumed());

        $this->setUpWith(array($this->generatorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkConsumed());
    }

    public function testConsumed()
    {
        $this->setUpWith(array());

        $this->assertEquals(
            new EventSequence(array(), $this->callVerifierFactory),
            $this->subject->never()->consumed()
        );

        $this->setUpWith($this->nonGeneratorCalls);

        $this->assertEquals(
            new EventSequence(array(), $this->callVerifierFactory),
            $this->subject->never()->consumed()
        );

        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->consumed()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->times(1)->consumed()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->once()->consumed()
        );

        $this->setUpWith(array($this->generatorCall));

        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->always()->consumed()
        );

        $this->generatorEndEvent = $this->eventFactory->createThrew($this->exceptionA);
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );
        $this->setUpWith(array($this->generatorCall));

        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->always()->consumed()
        );
    }

    public function testConsumedFailureNonIterables()
    {
        $this->setUpWith($this->nonGeneratorCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->consumed();
    }

    public function testConsumedFailureNeverConsumed()
    {
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents
        );
        $this->setUpWith(array($this->generatorCall));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->consumed();
    }

    public function testConsumedFailureAlways()
    {
        $this->setUpWith($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->consumed();
    }

    public function testConsumedFailureNever()
    {
        $this->setUpWith($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->consumed();
    }

    public function testCheckReturned()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkReturned());
        $this->assertFalse((boolean) $this->subject->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->checkReturned($this->returnValueA));
        $this->assertFalse((boolean) $this->subject->checkReturned($this->returnValueB));
        $this->assertFalse(
            (boolean) $this->subject->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((boolean) $this->subject->checkReturned('z'));

        $this->setUpWith($this->calls);

        $this->assertTrue((boolean) $this->subject->checkReturned());
        $this->assertFalse((boolean) $this->subject->checkReturned(null));
        $this->assertTrue((boolean) $this->subject->checkReturned('w'));
        $this->assertTrue((boolean) $this->subject->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((boolean) $this->subject->checkReturned('z'));
    }

    public function testReturned()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->returned()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->returned('w')
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorEndEvent), $this->callVerifierFactory),
            $this->subject->returned($this->matcherFactory->equalTo('w'))
        );
    }

    public function testReturnedFailure()
    {
        $this->setUpWith($this->calls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->returned('z');
    }

    public function testReturnedFailureWithoutMatcher()
    {
        $this->setUpWith($this->nonGeneratorCalls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->returned();
    }

    public function testCheckAlwaysReturned()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->always()->checkReturned());
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('w'));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('z'));

        $this->setUpWith($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkReturned());
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('w'));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('z'));

        $this->setUpWith(array($this->generatorCall, $this->generatorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkReturned());
        $this->assertTrue((boolean) $this->subject->always()->checkReturned('w'));
        $this->assertTrue((boolean) $this->subject->always()->checkReturned($this->matcherFactory->equalTo('w')));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('y'));
    }

    public function testAlwaysReturned()
    {
        $this->setUpWith(array($this->generatorCall, $this->generatorCall));
        $expected =
            new EventSequence(array($this->generatorEndEvent, $this->generatorEndEvent), $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->always()->returned());
        $this->assertEquals($expected, $this->subject->always()->returned('w'));
        $this->assertEquals($expected, $this->subject->always()->returned($this->matcherFactory->equalTo('w')));
    }

    public function testAlwaysReturnedFailure()
    {
        $this->setUpWith($this->calls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->returned('w');
    }

    public function testAlwaysReturnedFailureWithNoMatcher()
    {
        $this->setUpWith($this->calls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->returned();
    }

    public function testCheckThrew()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkThrew());
        $this->assertFalse((boolean) $this->subject->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith($this->callsWithThrow);

        $this->assertTrue((boolean) $this->subject->checkThrew());
        $this->assertTrue((boolean) $this->subject->checkThrew('Exception'));
        $this->assertTrue((boolean) $this->subject->checkThrew('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->checkThrew($this->exceptionA));
        $this->assertTrue((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->setUpWith(array());

        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against 111.');
        $this->subject->checkThrew(111);
    }

    public function testCheckThrewFailureInvalidInputObject()
    {
        $this->setUpWith(array());

        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against #0{}.');
        $this->subject->checkThrew((object) array());
    }

    public function testThrew()
    {
        $this->setUpWith($this->callsWithThrow);

        $this->assertEquals(
            new EventSequence(array($this->generatorThrewEvent), $this->callVerifierFactory),
            $this->subject->threw()
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorThrewEvent), $this->callVerifierFactory),
            $this->subject->threw('Exception')
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorThrewEvent), $this->callVerifierFactory),
            $this->subject->threw('RuntimeException')
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorThrewEvent), $this->callVerifierFactory),
            $this->subject->threw($this->exceptionA)
        );
        $this->assertEquals(
            new EventSequence(array($this->generatorThrewEvent), $this->callVerifierFactory),
            $this->subject->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testThrewWithEngineErrorException()
    {
        if (!$this->featureDetector->isSupported('error.exception.engine')) {
            $this->markTestSkipped('Requires engine error exceptions.');
        }

        $this->exceptionA = new Error('You done goofed.');
        $this->generatorThrewEvent = $this->eventFactory->createThrew($this->exceptionA);
        $this->generatorThrowCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorThrewEvent
        );

        $this->callsWithThrow = array(
            $this->calls[0],
            $this->generatorThrowCall,
        );
        $this->setUpWith($this->callsWithThrow);

        $this->assertEquals(
            new EventSequence(array($this->generatorThrewEvent), $this->callVerifierFactory),
            $this->subject->threw()
        );
    }

    public function testThrewWithInstanceHandle()
    {
        $builder = MockBuilderFactory::instance()->create('RuntimeException');
        $exception = $builder->get();
        $this->generatorThrewEvent = $this->eventFactory->createThrew($exception);
        $this->generatorThrowCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorThrewEvent
        );

        $this->callsWithThrow = array(
            $this->calls[0],
            $this->generatorThrowCall,
        );
        $this->setUpWith($this->callsWithThrow);
        $handle = Phony::on($exception);

        $this->assertTrue((boolean) $this->subject->threw($handle));
        $this->assertTrue((boolean) $this->subject->checkThrew($handle));
    }

    public function testThrewFailureExpectingAny()
    {
        $this->setUpWith($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw();
    }

    public function testThrewFailureExpectingType()
    {
        $this->setUpWith($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingException()
    {
        $this->setUpWith($this->callsWithThrow);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingMatcher()
    {
        $this->setUpWith($this->callsWithThrow);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->setUpWith(array());

        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against 111.');
        $this->subject->threw(111);
    }

    public function testThrewFailureInvalidInputObject()
    {
        $this->setUpWith(array());

        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against #0{}.');
        $this->subject->threw((object) array());
    }

    public function testCheckAlwaysThrew()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->always()->checkThrew());
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkThrew());
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith(array($this->generatorThrowCall, $this->generatorThrowCall));

        $this->assertTrue((boolean) $this->subject->always()->checkThrew());
        $this->assertTrue((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertTrue((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertTrue(
            (boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->setUpWith($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testAlwaysThrew()
    {
        $this->setUpWith(array($this->generatorThrowCall, $this->generatorThrowCall));
        $expected = new EventSequence(
            array($this->generatorThrewEvent, $this->generatorThrewEvent),
            $this->callVerifierFactory
        );

        $this->assertEquals($expected, $this->subject->always()->threw());
        $this->assertEquals($expected, $this->subject->always()->threw('Exception'));
        $this->assertEquals($expected, $this->subject->always()->threw('RuntimeException'));
        $this->assertEquals($expected, $this->subject->always()->threw($this->exceptionA));
        $this->assertEquals(
            $expected,
            $this->subject->always()->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testAlwaysThrewFailureExpectingAny()
    {
        $this->setUpWith($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingType()
    {
        $this->setUpWith(array($this->generatorThrowCall, $this->generatorThrowCall));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testAlwaysThrewFailureExpectingException()
    {
        $this->setUpWith(array($this->generatorThrowCall, $this->generatorThrowCall));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingMatcher()
    {
        $this->setUpWith(array($this->generatorThrowCall, $this->generatorThrowCall));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testCardinalityMethods()
    {
        $this->setUpWith(array());
        $this->subject->never();

        $this->assertEquals(new Cardinality(0, 0), $this->subject->never()->cardinality());
        $this->assertEquals(new Cardinality(1, 1), $this->subject->once()->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->times(2)->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->twice()->cardinality());
        $this->assertEquals(new Cardinality(3, 3), $this->subject->thrice()->cardinality());
        $this->assertEquals(new Cardinality(3), $this->subject->atLeast(3)->cardinality());
        $this->assertEquals(new Cardinality(null, 4), $this->subject->atMost(4)->cardinality());
        $this->assertEquals(new Cardinality(5, 6), $this->subject->between(5, 6)->cardinality());
        $this->assertEquals(new Cardinality(5, 6, true), $this->subject->between(5, 6)->always()->cardinality());
    }
}
