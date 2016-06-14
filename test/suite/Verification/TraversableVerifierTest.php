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
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class TraversableVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->eventFactory = $this->callFactory->eventFactory();
        $this->anyMatcher = new AnyMatcher();
        $this->wildcardAnyMatcher = WildcardMatcher::instance();
        $this->objectSequencer = new Sequencer();
        $this->exporter = new InlineExporter(1, $this->objectSequencer);
        $this->matcherFactory = new MatcherFactory($this->anyMatcher, $this->wildcardAnyMatcher, $this->exporter);

        $this->traversableCalledEvent = $this->eventFactory->createCalled();
        $this->returnedTraversableEvent =
            $this->eventFactory->createReturned(array('m' => 'n', 'p' => 'q', 'r' => 's', 'u' => 'v'));
        $this->iteratorUsedEvent = $this->eventFactory->createUsed();
        $this->iteratorEventA = $this->eventFactory->createProduced('m', 'n');
        $this->iteratorEventC = $this->eventFactory->createProduced('p', 'q');
        $this->iteratorEventE = $this->eventFactory->createProduced('r', 's');
        $this->iteratorEventG = $this->eventFactory->createProduced('u', 'v');
        $this->iteratorEvents = array(
            $this->iteratorUsedEvent,
            $this->iteratorEventA,
            $this->iteratorEventC,
            $this->iteratorEventE,
            $this->iteratorEventG,
        );
        $this->traversableEndEvent = $this->eventFactory->createConsumed();
        $this->iteratorCall = $this->callFactory->create(
            $this->traversableCalledEvent,
            $this->returnedTraversableEvent,
            $this->iteratorEvents,
            $this->traversableEndEvent
        );

        $this->returnedEvent = $this->eventFactory->createReturned(null);
        $this->calls = array(
            $this->callFactory->create(
                null,
                $this->returnedEvent,
                array(),
                $this->returnedEvent
            ),
            $this->iteratorCall,
        );
        $this->nonTraversableCalls = array(
            $this->callFactory->create(),
            $this->callFactory->create(),
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

        $this->typicalCallsPlusIteratorCall = $this->typicalCalls;
        $this->typicalCallsPlusIteratorCall[] = $this->iteratorCall;
    }

    private function setUpWith($calls)
    {
        $this->spyFactory = SpyFactory::instance();
        $this->spy = $this->spyFactory->create('implode')->setLabel('label');
        $this->spy->setCalls($calls);
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->matcherVerifier = new MatcherVerifier();
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
        $this->subject = new TraversableVerifier(
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

        $this->setUpWith($this->nonTraversableCalls);

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

        $this->setUpWith(array($this->iteratorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkUsed());
    }

    public function testUsed()
    {
        $this->setUpWith(array());

        $this->assertEquals(new EventSequence(array()), $this->subject->never()->used());

        $this->setUpWith($this->nonTraversableCalls);

        $this->assertEquals(new EventSequence(array()), $this->subject->never()->used());

        $this->setUpWith($this->calls);

        $this->assertEquals(new EventSequence(array($this->iteratorUsedEvent)), $this->subject->used());
        $this->assertEquals(new EventSequence(array($this->iteratorUsedEvent)), $this->subject->times(1)->used());
        $this->assertEquals(new EventSequence(array($this->iteratorUsedEvent)), $this->subject->once()->used());

        $this->setUpWith(array($this->iteratorCall));

        $this->assertEquals(new EventSequence(array($this->iteratorUsedEvent)), $this->subject->always()->used());
    }

    public function testUsedFailureNonTraversables()
    {
        $this->setUpWith($this->nonTraversableCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->used();
    }

    public function testUsedFailureNeverUsed()
    {
        $this->iteratorCall = $this->callFactory->create(
            $this->traversableCalledEvent,
            $this->returnedTraversableEvent
        );
        $this->setUpWith(array($this->iteratorCall));
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

        $this->setUpWith($this->nonTraversableCalls);

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

        $this->setUpWith(array($this->iteratorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkProduced());
    }

    public function testProduced()
    {
        $this->setUpWith($this->calls);

        $this->assertEquals(
            new EventSequence(
                array($this->iteratorEventA, $this->iteratorEventC, $this->iteratorEventE, $this->iteratorEventG)
            ),
            $this->subject->produced()
        );
        $this->assertEquals(new EventSequence(array($this->iteratorEventA)), $this->subject->produced('n'));
        $this->assertEquals(new EventSequence(array($this->iteratorEventA)), $this->subject->produced('m', 'n'));
        $this->assertEquals(
            new EventSequence(
                array($this->iteratorEventA, $this->iteratorEventC, $this->iteratorEventE, $this->iteratorEventG)
            ),
            $this->subject->times(1)->produced()
        );
        $this->assertEquals(
            new EventSequence(array($this->iteratorEventA)),
            $this->subject->once()->produced('n')
        );
        $this->assertEquals(new EventSequence(array()), $this->subject->never()->produced('m'));

        $this->setUpWith(array($this->iteratorCall));

        $this->assertEquals(
            new EventSequence(
                array($this->iteratorEventA, $this->iteratorEventC, $this->iteratorEventE, $this->iteratorEventG)
            ),
            $this->subject->always()->produced()
        );
    }

    public function testProducedFailureNoTraversablesNoMatchers()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced();
    }

    public function testProducedFailureNoTraversablesValueOnly()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x');
    }

    public function testProducedFailureNoTraversablesKeyAndValue()
    {
        $this->setUpWith($this->typicalCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x');
    }

    public function testProducedFailureKeyValueMismatch()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureAlways()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->produced('n');
    }

    public function testProducedFailureNever()
    {
        $this->setUpWith($this->typicalCallsPlusIteratorCall);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->produced('n');
    }

    public function testCheckConsumed()
    {
        $this->setUpWith(array());

        $this->assertFalse((boolean) $this->subject->checkConsumed());
        $this->assertFalse((boolean) $this->subject->times(1)->checkConsumed());
        $this->assertFalse((boolean) $this->subject->once()->checkConsumed());
        $this->assertTrue((boolean) $this->subject->never()->checkConsumed());

        $this->setUpWith($this->nonTraversableCalls);

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

        $this->setUpWith(array($this->iteratorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkConsumed());
    }

    public function testConsumed()
    {
        $this->setUpWith(array());

        $this->assertEquals(new EventSequence(array()), $this->subject->never()->consumed());

        $this->setUpWith($this->nonTraversableCalls);

        $this->assertEquals(new EventSequence(array()), $this->subject->never()->consumed());

        $this->setUpWith($this->calls);

        $this->assertEquals(new EventSequence(array($this->traversableEndEvent)), $this->subject->consumed());
        $this->assertEquals(new EventSequence(array($this->traversableEndEvent)), $this->subject->times(1)->consumed());
        $this->assertEquals(new EventSequence(array($this->traversableEndEvent)), $this->subject->once()->consumed());

        $this->setUpWith(array($this->iteratorCall));

        $this->assertEquals(new EventSequence(array($this->traversableEndEvent)), $this->subject->always()->consumed());
    }

    public function testConsumedFailureNonTraversables()
    {
        $this->setUpWith($this->nonTraversableCalls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->consumed();
    }

    public function testConsumedFailureNeverConsumed()
    {
        $this->iteratorCall = $this->callFactory->create(
            $this->traversableCalledEvent,
            $this->returnedTraversableEvent,
            $this->iteratorEvents
        );
        $this->setUpWith(array($this->iteratorCall));
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
