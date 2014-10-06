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
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestAssertionRecorder;
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
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->subject->startTime());
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
    public function testCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'calledWith'), $arguments));
        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'calledWith'), $matchers));
    }

    public function testCalledWithWithEmptyArguments()
    {
        $this->assertTrue($this->subject->calledWith());
    }

    public function testAssertCalledWith()
    {
        $this->assertNull($this->subject->assertCalledWith('a', 'b', 'c'));
        $this->assertNull($this->subject->assertCalledWith($this->matchers[0], $this->matchers[1], $this->matchers[2]));
        $this->assertNull($this->subject->assertCalledWith('a', 'b'));
        $this->assertNull($this->subject->assertCalledWith($this->matchers[0], $this->matchers[1]));
        $this->assertNull($this->subject->assertCalledWith('a'));
        $this->assertNull($this->subject->assertCalledWith($this->matchers[0]));
        $this->assertNull($this->subject->assertCalledWith());
        $this->assertSame(7, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledWithFailure()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'b'>, <'c'>, <any>*
Actual arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWith('b', 'c');
    }

    public function testAssertCalledWithFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'b'>, <'c'>, <any>*
Actual arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectWithNoArguments->assertCalledWith('b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'calledWithExactly'), $arguments)
        );
        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'calledWithExactly'), $matchers)
        );
    }

    public function testCalledWithWithExactlyEmptyArguments()
    {
        $this->assertFalse($this->subject->calledWithExactly());
    }

    public function testAssertCalledWithExactly()
    {
        $this->assertNull($this->subject->assertCalledWithExactly('a', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertCalledWithExactly($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledWithExactlyFailure()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'a'>, <'b'>
Actual arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWithExactly('a', 'b');
    }

    public function testAssertCalledWithExactlyFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'a'>, <'b'>
Actual arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectWithNoArguments->assertCalledWithExactly('a', 'b');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNotCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(!$calledWith, call_user_func_array(array($this->subject, 'notCalledWith'), $arguments));
        $this->assertSame(!$calledWith, call_user_func_array(array($this->subject, 'notCalledWith'), $matchers));
    }

    public function testNotCalledWithWithEmptyArguments()
    {
        $this->assertFalse($this->subject->notCalledWith());
    }

    public function testAssertNotCalledWith()
    {
        $this->assertNull($this->subject->assertNotCalledWith('b', 'c'));
        $this->assertNull($this->subject->assertNotCalledWith($this->matchers[1], $this->matchers[2]));
        $this->assertNull($this->subject->assertNotCalledWith('c'));
        $this->assertNull($this->subject->assertNotCalledWith($this->matchers[2]));
        $this->assertNull($this->subject->assertNotCalledWith('a', 'b', 'c', 'd'));
        $this->assertNull(
            $this->subject
                ->assertNotCalledWith($this->matchers[0], $this->matchers[1], $this->matchers[2], $this->otherMatcher)
        );
        $this->assertNull($this->subject->assertNotCalledWith('d', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertNotCalledWith($this->otherMatcher, $this->matchers[1], $this->matchers[2])
        );
        $this->assertNull($this->subject->assertNotCalledWith('a', 'b', 'd'));
        $this->assertNull(
            $this->subject->assertNotCalledWith($this->matchers[0], $this->matchers[1], $this->otherMatcher)
        );
        $this->assertNull($this->subject->assertNotCalledWith('d'));
        $this->assertNull($this->subject->assertNotCalledWith($this->otherMatcher));
        $this->assertSame(12, $this->assertionRecorder->successCount());
    }

    public function testAssertNotCalledWithFailure()
    {
        $expected = <<<'EOD'
Expected arguments unlike:
    <'a'>, <'b'>, <any>*
Actual arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNotCalledWith('a', 'b');
    }

    public function testAssertNotCalledWithFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected arguments unlike:
    <any>*
Actual arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectWithNoArguments->assertNotCalledWith();
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNotCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject, 'notCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject, 'notCalledWithExactly'), $matchers)
        );
    }

    public function testNotCalledWithExactlyWithEmptyArguments()
    {
        $this->assertTrue($this->subject->notCalledWithExactly());
    }

    public function testAssertNotCalledWithExactly()
    {
        $this->assertNull($this->subject->assertNotCalledWithExactly('a', 'b'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->matchers[0], $this->matchers[1]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('a'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->matchers[0]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('b', 'c'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->matchers[1], $this->matchers[2]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('c'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->matchers[2]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('a', 'b', 'c', 'd'));
        $this->assertNull(
            $this->subject->assertNotCalledWithExactly(
                $this->matchers[0],
                $this->matchers[1],
                $this->matchers[2],
                $this->otherMatcher
            )
        );
        $this->assertNull($this->subject->assertNotCalledWithExactly('d', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertNotCalledWithExactly($this->otherMatcher, $this->matchers[1], $this->matchers[2])
        );
        $this->assertNull($this->subject->assertNotCalledWithExactly('a', 'b', 'd'));
        $this->assertNull(
            $this->subject->assertNotCalledWithExactly($this->matchers[0], $this->matchers[1], $this->otherMatcher)
        );
        $this->assertNull($this->subject->assertNotCalledWithExactly('d'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->otherMatcher));
        $this->assertSame(16, $this->assertionRecorder->successCount());
    }

    public function testAssertNotCalledWithExactlyFailure()
    {
        $expected = <<<'EOD'
Expected arguments unlike:
    <'a'>, <'b'>, <'c'>
Actual arguments:
    'a', 'b', 'c'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNotCalledWithExactly('a', 'b', 'c');
    }

    public function testAssertNotCalledWithExactlyFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected arguments unlike:
    <none>
Actual arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectWithNoArguments->assertNotCalledWithExactly();
    }

    public function testCalledBefore()
    {
        $this->assertTrue($this->subject->calledBefore($this->lateCall));
        $this->assertFalse($this->subject->calledBefore($this->earlyCall));
    }

    public function testAssertCalledBefore()
    {
        $this->assertNull($this->subject->assertCalledBefore($this->lateCall));
        $this->assertSame(1, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledBeforeFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called before supplied call.'
        );
        $this->subject->assertCalledBefore($this->earlyCall);
    }

    public function testCalledAfter()
    {
        $this->assertTrue($this->subject->calledAfter($this->earlyCall));
        $this->assertFalse($this->subject->calledAfter($this->lateCall));
    }

    public function testAssertCalledAfter()
    {
        $this->assertNull($this->subject->assertCalledAfter($this->earlyCall));
        $this->assertSame(1, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledAfterFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called after supplied call.'
        );
        $this->subject->assertCalledAfter($this->lateCall);
    }

    public function testCalledOn()
    {
        $this->assertTrue($this->subject->calledOn($this->thisValue));
        $this->assertTrue($this->subject->calledOn(new EqualToMatcher($this->thisValue)));
        $this->assertFalse($this->subject->calledOn((object) array('property' => 'value')));
    }

    public function testAssertCalledOn()
    {
        $this->assertNull($this->subject->assertCalledOn($this->thisValue));
        $this->assertNull($this->subject->assertCalledOn(new EqualToMatcher($this->thisValue)));
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledOnFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Not called on expected object. Actual object was stdClass Object ()."
        );
        $this->subject->assertCalledOn((object) array());
    }

    public function testAssertCalledOnFailureWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Not called on object like <stdClass Object (...)>. " .
                "Actual object was stdClass Object ()."
        );
        $this->subject->assertCalledOn(new EqualToMatcher((object) array('property' => 'value')));
    }

    public function testReturned()
    {
        $this->assertTrue($this->subject->returned($this->returnValue));
        $this->assertTrue($this->subject->returned($this->matcherFactory->adapt($this->returnValue)));
        $this->assertFalse($this->subject->returned('y'));
        $this->assertFalse($this->subject->returned($this->matcherFactory->adapt('y')));
    }

    public function testAssertReturned()
    {
        $this->assertNull($this->subject->assertReturned($this->returnValue));
        $this->assertNull($this->subject->assertReturned($this->matcherFactory->adapt($this->returnValue)));
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertReturnedFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return value like <'x'>. Returned 'abc'."
        );
        $this->subject->assertReturned('x');
    }

    public function testThrew()
    {
        $this->assertFalse($this->subject->threw());
        $this->assertFalse($this->subject->threw('Exception'));
        $this->assertFalse($this->subject->threw('RuntimeException'));
        $this->assertFalse($this->subject->threw($this->exception));
        $this->assertFalse($this->subject->threw(new EqualToMatcher($this->exception)));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));
        $this->assertFalse($this->subject->threw(new EqualToMatcher(new RuntimeException())));
        $this->assertTrue($this->subject->threw(new EqualToMatcher(null)));
        $this->assertFalse($this->subject->threw(111));

        $this->assertTrue($this->subjectWithException->threw());
        $this->assertTrue($this->subjectWithException->threw('Exception'));
        $this->assertTrue($this->subjectWithException->threw('RuntimeException'));
        $this->assertTrue($this->subjectWithException->threw($this->exception));
        $this->assertTrue($this->subjectWithException->threw(new EqualToMatcher($this->exception)));
        $this->assertFalse($this->subjectWithException->threw('InvalidArgumentException'));
        $this->assertFalse($this->subjectWithException->threw(new Exception()));
        $this->assertFalse($this->subjectWithException->threw(new RuntimeException()));
        $this->assertFalse($this->subjectWithException->threw(new EqualToMatcher(new RuntimeException())));
        $this->assertFalse($this->subjectWithException->threw(new EqualToMatcher(null)));
        $this->assertFalse($this->subjectWithException->threw(111));
    }

    public function testAssertThrew()
    {
        $this->assertNull($this->subjectWithException->assertThrew());
        $this->assertNull($this->subjectWithException->assertThrew('Exception'));
        $this->assertNull($this->subjectWithException->assertThrew('RuntimeException'));
        $this->assertNull($this->subjectWithException->assertThrew($this->exception));
        $this->assertNull($this->subjectWithException->assertThrew(new EqualToMatcher($this->exception)));
        $this->assertSame(5, $this->assertionRecorder->successCount());
    }

    public function testAssertThrewFailureExpectingAnyNoneThrown()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', 'Nothing thrown.');
        $this->subject->assertThrew();
    }

    public function testAssertThrewFailureTypeMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'InvalidArgumentException' exception. " .
                "Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->assertThrew('InvalidArgumentException');
    }

    public function testAssertThrewFailureExpectingTypeNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'InvalidArgumentException' exception. Nothing thrown."
        );
        $this->subject->assertThrew('InvalidArgumentException');
    }

    public function testAssertThrewFailureExceptionMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception equal to RuntimeException(). " .
                "Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->assertThrew(new RuntimeException());
    }

    public function testAssertThrewFailureExpectingExceptionNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception equal to RuntimeException(). Nothing thrown."
        );
        $this->subject->assertThrew(new RuntimeException());
    }

    public function testAssertThrewFailureMatcherMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception like <RuntimeException Object (...)>. " .
                "Threw RuntimeException('You done goofed.')."
        );
        $this->subjectWithException->assertThrew(new EqualToMatcher(new RuntimeException()));
    }

    public function testAssertThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subjectWithException->assertThrew((object) array());
    }
}
