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

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestAssertionRecorder;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class CallVerifierWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callFactory->sequencer()->set(111);
        $this->thisValue = (object) array();
        $this->callback = array($this->thisValue, 'implode');
        $this->arguments = array('a', 'b', 'c');
        $this->returnValue = 'abc';
        $this->calledEvent = $this->callFactory->createCalledEvent($this->callback, $this->arguments);
        $this->returnedEvent = $this->callFactory->createReturnedEvent($this->returnValue);
        $this->call = $this->callFactory->create($this->calledEvent, $this->returnedEvent);

        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = new TestAssertionRecorder();
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
        $this->threwEvent = $this->callFactory->createThrewEvent($this->exception);
        $this->callWithException = $this->callFactory->create($this->calledEvent, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->calledEventWithNoArguments = $this->callFactory->createCalledEvent($this->callback);
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

        $this->calledEventWithNoArguments = $this->callFactory->createCalledEvent($this->callback);
        $this->callWithNoResponse = $this->callFactory->create($this->calledEvent);
        $this->subjectWithNoResponse = new CallVerifier(
            $this->callWithNoResponse,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callFactory->sequencer()->reset();
        $this->earlyCall = $this->callFactory->create();
        $this->callFactory->sequencer()->set(222);
        $this->lateCall = $this->callFactory->create();
    }

    public function testProxyMethodsWithGeneratorEvents()
    {
        $generatedEvent = $this->callFactory->createGeneratedEvent();
        $generatorEventA = $this->callFactory->createYieldedEvent();
        $generatorEventB = $this->callFactory->createSentEvent();
        $generatorEvents = array($generatorEventA, $generatorEventB);
        $endEvent = $this->callFactory->createReturnedEvent();
        $this->call = new Call($this->calledEvent, $generatedEvent, $generatorEvents, $endEvent);
        $this->events = array($this->calledEvent, $generatedEvent, $generatorEventA, $generatorEventB, $endEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($generatedEvent, $this->subject->responseEvent());
        $this->assertSame($generatorEvents, $this->subject->generatorEvents());
        $this->assertSame($endEvent, $this->subject->endEvent());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertInstanceOf('Generator', $this->subject->returnValue());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->subject->startTime());
        $this->assertSame($generatedEvent->time(), $this->subject->responseTime());
        $this->assertSame($endEvent->time(), $this->subject->endTime());
        $this->assertNull($this->subject->exception());
    }

    public function testAddGeneratorEvent()
    {
        $generatedEvent = $this->callFactory->createGeneratedEvent();
        $generatorEventA = $this->callFactory->createYieldedEvent();
        $generatorEventB = $this->callFactory->createSentEvent();
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
        $this->calledEvent = $this->callFactory->createCalledEvent();
        $generatedEvent = $this->callFactory->createGeneratedEvent();
        $this->call = new Call($this->calledEvent, $generatedEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->assertEquals(1, $this->subject->responseDuration());
        $this->assertNull($this->subjectWithNoResponse->duration());
    }
}
