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
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class CallVerifierTest extends PHPUnit_Framework_TestCase
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
    }

    public function testConstructor()
    {
        $this->assertSame($this->call, $this->subject->call());
        $this->assertEquals($this->duration, $this->subject->duration());
        $this->assertSame($this->argumentCount, $this->subject->argumentCount());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
        $this->assertSame(array(1, null), $this->subject->cardinality());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallVerifier($this->call);

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->generatorEvents());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->subject->time());
        $this->assertSame($this->returnedEvent->time(), $this->subject->responseTime());
        $this->assertSame($this->returnedEvent->time(), $this->subject->endTime());
        $this->assertNull($this->subject->exception());
    }

    public function testSetResponseEvent()
    {
        $this->subjectWithNoResponse->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subjectWithNoResponse->responseEvent());
        $this->assertSame($this->returnedEvent, $this->subjectWithNoResponse->endEvent());
    }

    public function testSetEndEvent()
    {
        $this->subjectWithNoResponse->setEndEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subjectWithNoResponse->endEvent());
        $this->assertSame($this->returnedEvent, $this->subjectWithNoResponse->responseEvent());
    }

    public function testDuration()
    {
        $this->assertEquals($this->duration, $this->subject->duration());
        $this->assertNull($this->subjectWithNoResponse->duration());
    }

    public function testResponseDuration()
    {
        $this->assertEquals($this->duration, $this->subject->responseDuration());
        $this->assertNull($this->subjectWithNoResponse->responseDuration());
    }

    public function calledWithData()
    {
        //                                    arguments                  calledWith calledWithExactly
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),      true,      true),
            'First arguments'        => array(array('a', 'b'),           true,      false),
            'Single argument'        => array(array('a'),                true,      false),
            'Last arguments'         => array(array('b', 'c'),           false,     false),
            'Last argument'          => array(array('c'),                false,     false),
            'Extra arguments'        => array(array('a', 'b', 'c', 'd'), false,     false),
            'First argument differs' => array(array('d', 'b', 'c'),      false,     false),
            'Last argument differs'  => array(array('a', 'b', 'd'),      false,     false),
            'Unused argument'        => array(array('d'),                false,     false),
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'checkCalledWith'), $arguments));
        $this->assertSame(
            !$calledWith,
            call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $arguments)
        );
        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'checkCalledWith'), $matchers));
        $this->assertSame(
            !$calledWith,
            call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $matchers)
        );
    }

    public function testCheckCalledWithWithEmptyArguments()
    {
        $this->assertTrue($this->subject->checkCalledWith());
    }

    public function testCalledWith()
    {
        $this->assertEquals($this->assertionResult, $this->subject->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $this->assertionResult,
            $this->subject->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertEquals($this->assertionResult, $this->subject->calledWith('a', 'b'));
        $this->assertEquals(
            $this->assertionResult,
            $this->subject->calledWith($this->matchers[0], $this->matchers[1])
        );
        $this->assertEquals($this->assertionResult, $this->subject->calledWith('a'));
        $this->assertEquals($this->assertionResult, $this->subject->calledWith($this->matchers[0]));
        $this->assertEquals($this->assertionResult, $this->subject->calledWith());

        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->calledWith('b', 'c'));
    }

    public function testCalledWithFailure()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    <'b'>, <'c'>, <any>*
Arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureNever()
    {
        $expected = <<<'EOD'
Expected 0 calls with arguments like:
    <'a'>, <any>*
Arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->calledWith('a');
    }

    public function testCalledWithFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    <'b'>, <'c'>, <any>*
Arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectWithNoArguments->calledWith('b', 'c');
    }

    public function testCalledWithFailureInvalidCardinality()
    {
        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException');
        $this->subject->times(2)->calledWith('a');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'checkCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject->never(), 'checkCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'checkCalledWithExactly'), $matchers)
        );
        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject->never(), 'checkCalledWithExactly'), $matchers)
        );
    }

    public function testCheckCalledWithWithExactlyEmptyArguments()
    {
        $this->assertFalse($this->subject->checkCalledWithExactly());
    }

    public function testCalledWithExactly()
    {
        $this->assertEquals($this->assertionResult, $this->subject->calledWithExactly('a', 'b', 'c'));
        $this->assertEquals(
            $this->assertionResult,
            $this->subject->calledWithExactly($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );

        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->calledWithExactly('a'));
    }

    public function testCalledWithExactlyFailure()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    <'a'>, <'b'>
Arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWithExactly('a', 'b');
    }

    public function testCalledWithExactlyFailureNever()
    {
        $expected = <<<'EOD'
Expected 0 calls with arguments like:
    <'a'>, <'b'>, <'c'>
Arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->calledWithExactly('a', 'b', 'c');
    }

    public function testCalledWithExactlyFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    <'a'>, <'b'>
Arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectWithNoArguments->calledWithExactly('a', 'b');
    }

    public function testCalledWithExactlyFailureInvalidCardinality()
    {
        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException');
        $this->subject->times(2)->calledWithExactly('a', 'b', 'c');
    }

    public function testCheckCalledBefore()
    {
        $this->assertTrue($this->subject->checkCalledBefore($this->lateCall));
        $this->assertTrue($this->subject->never()->checkCalledBefore($this->earlyCall));
        $this->assertFalse($this->subject->checkCalledBefore($this->earlyCall));
        $this->assertFalse($this->subject->never()->checkCalledBefore($this->lateCall));
    }

    public function testCalledBefore()
    {
        $this->assertEquals($this->assertionResult, $this->subject->calledBefore($this->lateCall));
        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->calledBefore($this->earlyCall));
    }

    public function testCalledBeforeFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called before supplied call.'
        );
        $this->subject->calledBefore($this->earlyCall);
    }

    public function testCalledBeforeFailureNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Called before supplied call.'
        );
        $this->subject->never()->calledBefore($this->lateCall);
    }

    public function testCheckCalledAfter()
    {
        $this->assertTrue($this->subject->checkCalledAfter($this->earlyCall));
        $this->assertTrue($this->subject->never()->checkCalledAfter($this->lateCall));
        $this->assertFalse($this->subject->checkCalledAfter($this->lateCall));
        $this->assertFalse($this->subject->never()->checkCalledAfter($this->earlyCall));
    }

    public function testCalledAfter()
    {
        $this->assertEquals($this->assertionResult, $this->subject->calledAfter($this->earlyCall));
        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->calledAfter($this->lateCall));
    }

    public function testCalledAfterFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called after supplied call.'
        );
        $this->subject->calledAfter($this->lateCall);
    }

    public function testCalledAfterFailureNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Called after supplied call.'
        );
        $this->subject->never()->calledAfter($this->earlyCall);
    }

    public function testCheckCalledOn()
    {
        $this->assertTrue($this->subject->checkCalledOn($this->thisValue));
        $this->assertTrue($this->subject->checkCalledOn(new EqualToMatcher($this->thisValue)));
        $this->assertTrue($this->subject->never()->checkCalledOn((object) array('property' => 'value')));
        $this->assertFalse($this->subject->checkCalledOn((object) array('property' => 'value')));
        $this->assertFalse($this->subject->never()->checkCalledOn($this->thisValue));
    }

    public function testCalledOn()
    {
        $this->assertEquals($this->assertionResult, $this->subject->calledOn($this->thisValue));
        $this->assertEquals($this->assertionResult, $this->subject->calledOn(new EqualToMatcher($this->thisValue)));
        $this->assertEquals(
            $this->emptyAssertionResult,
            $this->subject->never()->calledOn((object) array('property' => 'value'))
        );
    }

    public function testCalledOnFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Not called on expected object. Object was stdClass Object ()."
        );
        $this->subject->calledOn((object) array());
    }

    public function testCalledOnFailureNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Called on unexpected object. Object was stdClass Object ()."
        );
        $this->subject->never()->calledOn($this->thisValue);
    }

    public function testCalledOnFailureWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Not called on object like <stdClass Object (...)>. Object was stdClass Object ()."
        );
        $this->subject->calledOn(new EqualToMatcher((object) array('property' => 'value')));
    }

    public function testCalledOnFailureWithMatcherNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Called on object like <stdClass Object ()>. Object was stdClass Object ()."
        );
        $this->subject->never()->calledOn(new EqualToMatcher($this->thisValue));
    }

    public function testCheckReturned()
    {
        $this->assertTrue($this->subject->checkReturned());
        $this->assertTrue($this->subject->checkReturned($this->returnValue));
        $this->assertTrue($this->subject->checkReturned($this->matcherFactory->adapt($this->returnValue)));
        $this->assertFalse($this->subject->checkReturned(null));
        $this->assertFalse($this->subject->checkReturned('y'));
        $this->assertFalse($this->subject->checkReturned($this->matcherFactory->adapt('y')));
        $this->assertFalse($this->subjectWithException->checkReturned());
        $this->assertFalse($this->subjectWithNoResponse->checkReturned());
    }

    public function testReturned()
    {
        $this->assertEquals($this->returnedAssertionResult, $this->subject->returned());
        $this->assertEquals($this->returnedAssertionResult, $this->subject->returned($this->returnValue));
        $this->assertEquals(
            $this->returnedAssertionResult,
            $this->subject->returned($this->matcherFactory->adapt($this->returnValue))
        );
    }

    public function testReturnedFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return like <'x'>. Returned 'abc'."
        );
        $this->subject->returned('x');
    }

    public function testReturnedFailureWithException()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return like <'x'>. Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->returned('x');
    }

    public function testReturnedFailureWithExceptionWithoutMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return. Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->returned();
    }

    public function testReturnedFailureNeverResponded()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return like <'x'>. Never responded."
        );
        $this->subjectWithNoResponse->returned('x');
    }

    public function testReturnedFailureNeverRespondedWithNoMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return. Never responded."
        );
        $this->subjectWithNoResponse->returned();
    }

    public function testCheckThrew()
    {
        $this->assertFalse($this->subject->checkThrew());
        $this->assertFalse($this->subject->checkThrew('Exception'));
        $this->assertFalse($this->subject->checkThrew('RuntimeException'));
        $this->assertFalse($this->subject->checkThrew($this->exception));
        $this->assertFalse($this->subject->checkThrew(new EqualToMatcher($this->exception)));
        $this->assertFalse($this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->checkThrew(new Exception()));
        $this->assertFalse($this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse($this->subject->checkThrew(new EqualToMatcher(new RuntimeException())));
        $this->assertFalse($this->subject->checkThrew(new EqualToMatcher(null)));

        $this->assertTrue($this->subjectWithException->checkThrew());
        $this->assertTrue($this->subjectWithException->checkThrew('Exception'));
        $this->assertTrue($this->subjectWithException->checkThrew('RuntimeException'));
        $this->assertTrue($this->subjectWithException->checkThrew($this->exception));
        $this->assertTrue($this->subjectWithException->checkThrew(new EqualToMatcher($this->exception)));
        $this->assertFalse($this->subjectWithException->checkThrew('InvalidArgumentException'));
        $this->assertFalse($this->subjectWithException->checkThrew(new Exception()));
        $this->assertFalse($this->subjectWithException->checkThrew(new RuntimeException()));
        $this->assertFalse($this->subjectWithException->checkThrew(new EqualToMatcher(new RuntimeException())));
        $this->assertFalse($this->subjectWithException->checkThrew(new EqualToMatcher(null)));
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subjectWithException->checkThrew((object) array());
    }

    public function testThrew()
    {
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw());
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw('Exception'));
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw('RuntimeException'));
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw($this->exception));
        $this->assertEquals(
            $this->threwAssertionResult,
            $this->subjectWithException->threw(new EqualToMatcher($this->exception))
        );
    }

    public function testThrewFailureExpectingAnyNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception. Returned 'abc'."
        );
        $this->subject->threw();
    }

    public function testThrewFailureTypeMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'InvalidArgumentException' exception. Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->threw('InvalidArgumentException');
    }

    public function testThrewFailureExpectingTypeNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'InvalidArgumentException' exception. Returned 'abc'."
        );
        $this->subject->threw('InvalidArgumentException');
    }

    public function testThrewFailureExceptionMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception equal to RuntimeException(). Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception equal to RuntimeException(). Returned 'abc'."
        );
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureMatcherMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception like <RuntimeException Object (...)>. Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subjectWithException->threw((object) array());
    }

    public function testCardinalityMethods()
    {
        $this->subject->never();

        $this->assertSame(array(0, 0), $this->subject->never()->cardinality());
        $this->assertSame(array(1, 1), $this->subject->once()->cardinality());
        $this->assertSame(array(2, 2), $this->subject->times(2)->cardinality());
        $this->assertSame(array(3, null), $this->subject->atLeast(3)->cardinality());
        $this->assertSame(array(null, 4), $this->subject->atMost(4)->cardinality());
        $this->assertSame(array(5, 6), $this->subject->between(5, 6)->cardinality());
        $this->assertSame(array(5, 6), $this->subject->between(6, 5)->cardinality());
    }
}
