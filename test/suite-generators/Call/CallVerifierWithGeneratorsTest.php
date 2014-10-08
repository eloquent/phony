<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Result\AssertionResult;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
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
        $this->thisValue = (object) array();
        $this->callback = array($this->thisValue, 'implode');
        $this->arguments = array('a', 'b', 'c');
        $this->returnValue = 'abc';
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->call = $this->callFactory->create($this->calledEvent, $this->returnedEvent);

        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->duration = $this->returnedEvent->time() - $this->calledEvent->time();
        $this->argumentCount = count($this->arguments);
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments);
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->events = array($this->calledEvent, $this->returnedEvent);

        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException = $this->callFactory->create($this->calledEvent, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
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
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callEventFactory->sequencer()->reset();
        $this->earlyCall = $this->callFactory->create();
        $this->callEventFactory->sequencer()->set(222);
        $this->lateCall = $this->callFactory->create();

        $this->assertionResult = new AssertionResult(array($this->call));
        $this->returnedAssertionResult = new AssertionResult(array($this->call->responseEvent()));
        $this->threwAssertionResult = new AssertionResult(array($this->callWithException->responseEvent()));
        $this->emptyAssertionResult = new AssertionResult();

        // additions for generators

        $this->sentException = new RuntimeException('Consequences will never be the same.');
        $this->generatedEvent = $this->callEventFactory->createGenerated();
        $this->generatorEventA = $this->callEventFactory->createYielded('m', 'n');
        $this->generatorEventB = $this->callEventFactory->createSent('o');
        $this->generatorEventC = $this->callEventFactory->createYielded('p', 'q');
        $this->generatorEventD = $this->callEventFactory->createSentException($this->sentException);
        $this->generatorEventE = $this->callEventFactory->createYielded('r', 's');
        $this->generatorEventF = $this->callEventFactory->createSent('t');
        $this->generatorEvents = array(
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
        );
        $this->generatorEndEvent = $this->callEventFactory->createReturned();
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
            $this->generatorEndEvent,
        );
        $this->generatorSubject = new CallVerifier(
            $this->generatorCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    public function testProxyMethodsWithGeneratorEvents()
    {
        $this->assertSame($this->calledEvent, $this->generatorSubject->calledEvent());
        $this->assertSame($this->generatedEvent, $this->generatorSubject->responseEvent());
        $this->assertSame($this->generatorEvents, $this->generatorSubject->generatorEvents());
        $this->assertSame($this->generatorEndEvent, $this->generatorSubject->endEvent());
        $this->assertSame($this->generatorCallEvents, $this->generatorSubject->events());
        $this->assertTrue($this->generatorSubject->hasResponded());
        $this->assertTrue($this->generatorSubject->isGenerator());
        $this->assertTrue($this->generatorSubject->hasCompleted());
        $this->assertSame($this->callback, $this->generatorSubject->callback());
        $this->assertSame($this->arguments, $this->generatorSubject->arguments());
        $this->assertInstanceOf('Generator', $this->generatorSubject->returnValue());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->generatorSubject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->generatorSubject->time());
        $this->assertSame($this->generatedEvent->time(), $this->generatorSubject->responseTime());
        $this->assertSame($this->generatorEndEvent->time(), $this->generatorSubject->endTime());
        $this->assertNull($this->generatorSubject->exception());
    }

    public function testAddGeneratorEvent()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $generatorEventA = $this->callEventFactory->createYielded();
        $generatorEventB = $this->callEventFactory->createSent();
        $generatorEvents = array($generatorEventA, $generatorEventB);
        $this->call = new Call($this->calledEvent, $generatedEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $this->subject->addGeneratorEvent($generatorEventA);
        $this->subject->addGeneratorEvent($generatorEventB);

        $this->assertSame($generatorEvents, $this->subject->generatorEvents());
    }

    public function testDurationMethodsWithGeneratorEvents()
    {
        $this->assertEquals(7, $this->generatorSubject->responseDuration());
        $this->assertNull($this->subjectWithNoResponse->duration());
    }

    public function testYielded()
    {
        $this->assertTrue($this->generatorSubject->yielded());
        $this->assertTrue($this->generatorSubject->yielded('n'));
        $this->assertTrue($this->generatorSubject->yielded('m', 'n'));
        $this->assertFalse($this->generatorSubject->yielded('m'));
        $this->assertFalse($this->generatorSubject->yielded('m', 'o'));
        $this->assertFalse($this->subject->yielded());
    }

    public function testAssertYielded()
    {
        $this->assertEquals(
            new AssertionResult(array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE)),
            $this->generatorSubject->assertYielded()
        );
        $this->assertEquals(
            new AssertionResult(array($this->generatorEventA)),
            $this->generatorSubject->assertYielded('n')
        );
        $this->assertEquals(
            new AssertionResult(array($this->generatorEventA)),
            $this->generatorSubject->assertYielded('m', 'n')
        );
    }

    public function testAssertYieldedFailureWithNoMatchers()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield. Generated nothing."
        );
        $this->subject->assertYielded();
    }

    public function testAssertYieldedFailureWithValueOnly()
    {
        $expected = <<<'EOD'
Expected yield like <'m'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->assertYielded('m');
    }

    public function testAssertYieldedFailureWithValueOnlyWithNoGeneratorEvents()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield like <'n'>. Generated nothing."
        );
        $this->subject->assertYielded('n');
    }

    public function testAssertYieldedFailureWithKeyAndValue()
    {
        $expected = <<<'EOD'
Expected yield like <'m'> => <'o'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->assertYielded('m', 'o');
    }

    public function testAssertYieldedFailureWithKeyAndValueWithNoGeneratorEvents()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield like <'m'> => <'n'>. Generated nothing."
        );
        $this->subject->assertYielded('m', 'n');
    }
}
