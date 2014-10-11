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
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\EqualToMatcher;
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

        $this->assertionResult = new EventCollection(array($this->call));
        $this->returnedAssertionResult = new EventCollection(array($this->call->responseEvent()));
        $this->threwAssertionResult = new EventCollection(array($this->callWithException->responseEvent()));
        $this->emptyAssertionResult = new EventCollection();

        // additions for generators

        $this->sentExceptionA = new RuntimeException('Consequences will never be the same.');
        $this->sentExceptionB = new RuntimeException('Because I backtraced it.');
        $this->generatedEvent = $this->callEventFactory->createGenerated();
        $this->generatorEventA = $this->callEventFactory->createYielded('m', 'n');
        $this->generatorEventB = $this->callEventFactory->createSent('o');
        $this->generatorEventC = $this->callEventFactory->createYielded('p', 'q');
        $this->generatorEventD = $this->callEventFactory->createSentException($this->sentExceptionA);
        $this->generatorEventE = $this->callEventFactory->createYielded('r', 's');
        $this->generatorEventF = $this->callEventFactory->createSent('t');
        $this->generatorEventG = $this->callEventFactory->createYielded('u', 'v');
        $this->generatorEventH = $this->callEventFactory->createSentException($this->sentExceptionB);
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
            $this->generatorEventG,
            $this->generatorEventH,
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

    public function testCheckYielded()
    {
        $this->assertTrue((boolean) $this->generatorSubject->checkYielded());
        $this->assertTrue((boolean) $this->generatorSubject->checkYielded('n'));
        $this->assertTrue((boolean) $this->generatorSubject->checkYielded('m', 'n'));
        $this->assertTrue((boolean) $this->generatorSubject->times(4)->checkYielded());
        $this->assertTrue((boolean) $this->generatorSubject->once()->checkYielded('n'));
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkYielded('m'));
        $this->assertFalse((boolean) $this->generatorSubject->checkYielded('m'));
        $this->assertFalse((boolean) $this->generatorSubject->checkYielded('m', 'o'));
        $this->assertFalse((boolean) $this->subject->checkYielded());
    }

    public function testYielded()
    {
        $this->assertEquals(
            new EventCollection(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG)
            ),
            $this->generatorSubject->yielded()
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventA)),
            $this->generatorSubject->yielded('n')
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventA)),
            $this->generatorSubject->yielded('m', 'n')
        );
        $this->assertEquals(
            new EventCollection(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG)
            ),
            $this->generatorSubject->times(4)->yielded()
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventA)),
            $this->generatorSubject->once()->yielded('n')
        );
        $this->assertEquals(new EventCollection(), $this->generatorSubject->never()->yielded('m'));
    }

    public function testYieldedFailureWithNoMatchers()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected call to yield. Generated nothing."
        );
        $this->subject->yielded();
    }

    public function testYieldedFailureWithNoMatchersNever()
    {
        $expected = <<<'EOD'
Expected no call to yield. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->yielded();
    }

    public function testYieldedFailureWithValueOnly()
    {
        $expected = <<<'EOD'
Expected yield to be like <'m'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->yielded('m');
    }

    public function testYieldedFailureWithValueOnlyNever()
    {
        $expected = <<<'EOD'
Expected no yield to be like <'n'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->yielded('n');
    }

    public function testYieldedFailureWithValueOnlyAlways()
    {
        $expected = <<<'EOD'
Expected every yield to be like <'n'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->yielded('n');
    }

    public function testYieldedFailureWithValueOnlyWithNoGeneratorEvents()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield to be like <'n'>. Generated nothing."
        );
        $this->subject->yielded('n');
    }

    public function testYieldedFailureWithKeyAndValue()
    {
        $expected = <<<'EOD'
Expected yield to be like <'m'> => <'o'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->yielded('m', 'o');
    }

    public function testYieldedFailureWithKeyAndValueNever()
    {
        $expected = <<<'EOD'
Expected no yield to be like <'m'> => <'n'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->yielded('m', 'n');
    }

    public function testYieldedFailureWithKeyAndValueWithNoGeneratorEvents()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield to be like <'m'> => <'n'>. Generated nothing."
        );
        $this->subject->yielded('m', 'n');
    }

    public function testCheckSent()
    {
        $this->assertTrue((boolean) $this->generatorSubject->checkSent());
        $this->assertTrue((boolean) $this->generatorSubject->checkSent('o'));
        $this->assertTrue((boolean) $this->generatorSubject->times(2)->checkSent());
        $this->assertTrue((boolean) $this->generatorSubject->once()->checkSent('o'));
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkSent('x'));
        $this->assertFalse((boolean) $this->generatorSubject->always()->checkSent());
        $this->assertFalse((boolean) $this->generatorSubject->checkSent('x'));
        $this->assertFalse((boolean) $this->subject->checkSent());
    }

    public function testSent()
    {
        $this->assertEquals(
            new EventCollection(array($this->generatorEventB, $this->generatorEventF)),
            $this->generatorSubject->sent()
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventB)),
            $this->generatorSubject->sent('o')
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventB, $this->generatorEventF)),
            $this->generatorSubject->times(2)->sent()
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventB)),
            $this->generatorSubject->once()->sent('o')
        );
        $this->assertEquals(new EventCollection(), $this->generatorSubject->never()->sent('x'));
    }

    public function testSentFailureNoMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield to be sent value. Generated nothing."
        );
        $this->subject->sent();
    }

    public function testSentFailureWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield to be sent value like <'x'>. Generated nothing."
        );
        $this->subject->sent('x');
    }

    public function testSentFailureWithNoMatchersNever()
    {
        $expected = <<<'EOD'
Expected no yield to be sent value. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->sent();
    }

    public function testSentFailureWithMatcherNever()
    {
        $expected = <<<'EOD'
Expected no yield to be sent value like <'o'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->sent('o');
    }

    public function testSentFailureWithNoMatcherAlways()
    {
        $expected = <<<'EOD'
Expected every yield to be sent value. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->sent();
    }

    public function testSentFailureWithMatcherAlways()
    {
        $expected = <<<'EOD'
Expected every yield to be sent value like <'o'>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->sent('o');
    }

    public function testCheckSentException()
    {
        $this->assertTrue((boolean) $this->generatorSubject->checkSentException());
        $this->assertTrue((boolean) $this->generatorSubject->checkSentException('Exception'));
        $this->assertTrue((boolean) $this->generatorSubject->checkSentException('RuntimeException'));
        $this->assertTrue((boolean) $this->generatorSubject->checkSentException($this->sentExceptionA));
        $this->assertTrue((boolean) $this->generatorSubject->checkSentException($this->sentExceptionB));
        $this->assertTrue(
            (boolean) $this->generatorSubject->checkSentException(new EqualToMatcher($this->sentExceptionA))
        );
        $this->assertFalse((boolean) $this->generatorSubject->checkSentException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->generatorSubject->checkSentException(new Exception()));
        $this->assertFalse((boolean) $this->generatorSubject->checkSentException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->generatorSubject->checkSentException(new EqualToMatcher(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->generatorSubject->checkSentException(new EqualToMatcher(null)));
        $this->assertFalse((boolean) $this->generatorSubject->never()->checkSentException());
        $this->assertFalse((boolean) $this->generatorSubject->never()->checkSentException('Exception'));
        $this->assertFalse((boolean) $this->generatorSubject->never()->checkSentException('RuntimeException'));
        $this->assertFalse((boolean) $this->generatorSubject->never()->checkSentException($this->sentExceptionA));
        $this->assertFalse(
            (boolean) $this->generatorSubject->never()->checkSentException(new EqualToMatcher($this->sentExceptionA))
        );
    }

    public function testCheckSentExceptionFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against 111."
        );
        $this->generatorSubject->checkSentException(111);
    }

    public function testCheckSentExceptionFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->generatorSubject->checkSentException((object) array());
    }

    public function testSentException()
    {
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->generatorSubject->sentException()
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->generatorSubject->sentException('Exception')
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->generatorSubject->sentException('RuntimeException')
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD)),
            $this->generatorSubject->sentException($this->sentExceptionA)
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventH)),
            $this->generatorSubject->sentException($this->sentExceptionB)
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD)),
            $this->generatorSubject->sentException(new EqualToMatcher($this->sentExceptionA))
        );
    }

    public function testSentExceptionFailureExpectingAnyNoneSent()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield to be sent exception. Generated nothing."
        );
        $this->subject->sentException();
    }

    public function testSentExceptionFailureExpectingAnyNoResponse()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected yield to be sent exception. Generated nothing."
        );
        $this->subject->sentException();
    }

    public function testSentExceptionFailureExpectingNeverAny()
    {
        $expected = <<<'EOD'
Expected no yield to be sent exception. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->sentException();
    }

    public function testSentExceptionFailureExpectingAlwaysAny()
    {
        $expected = <<<'EOD'
Expected every yield to be sent exception. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->sentException();
    }

    public function testSentExceptionFailureTypeMismatch()
    {
        $expected = <<<'EOD'
Expected yield to be sent 'InvalidArgumentException' exception. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->sentException('InvalidArgumentException');
    }

    public function testSentExceptionFailureTypeNever()
    {
        $expected = <<<'EOD'
Expected no yield to be sent 'RuntimeException' exception. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->sentException('RuntimeException');
    }

    public function testSentExceptionFailureExpectingTypeNoneSent()
    {
        $expected = <<<'EOD'
Expected yield to be sent 'InvalidArgumentException' exception. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->sentException('InvalidArgumentException');
    }

    public function testSentExceptionFailureExceptionMismatch()
    {
        $expected = <<<'EOD'
Expected yield to be sent exception equal to RuntimeException(). Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->sentException(new RuntimeException());
    }

    public function testSentExceptionFailureExceptionNever()
    {
        $expected = <<<'EOD'
Expected no yield to be sent exception equal to RuntimeException('Consequences will never be the same.'). Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->sentException($this->sentExceptionA);
    }

    public function testSentExceptionFailureExpectingExceptionNoneSent()
    {
        $expected = <<<'EOD'
Expected yield to be sent exception equal to RuntimeException(). Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->sentException(new RuntimeException());
    }

    public function testSentExceptionFailureMatcherMismatch()
    {
        $expected = <<<'EOD'
Expected yield to be sent exception like <RuntimeException Object (...)>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->sentException(new EqualToMatcher(new RuntimeException()));
    }

    public function testSentExceptionFailureMatcherNever()
    {
        $expected = <<<'EOD'
Expected no yield to be sent exception like <RuntimeException Object (...)>. Generated:
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
    - yielded 'u' => 'v'
    - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->sentException(new EqualToMatcher($this->sentExceptionA));
    }

    public function testSentExceptionFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against 111."
        );
        $this->generatorSubject->sentException(111);
    }

    public function testSentExceptionFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->generatorSubject->sentException((object) array());
    }
}
