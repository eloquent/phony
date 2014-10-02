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
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestAssertionRecorder;
use Eloquent\Phony\Test\TestCallEvent;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use RuntimeException;

class CallVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->reflector = new ReflectionMethod(__METHOD__);
        $this->thisValue = (object) array();
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
        $this->sequenceNumber = 111;
        $this->startTime = 1.11;
        $this->calledEvent = new CalledEvent(
            $this->reflector,
            $this->thisValue,
            $this->arguments,
            $this->sequenceNumber,
            $this->startTime
        );
        $this->returnValue = 'returnValue';
        $this->endTime = 2.22;
        $this->returnedEvent = new ReturnedEvent($this->returnValue, $this->sequenceNumber + 1, $this->endTime);
        $this->eventA = new TestCallEvent($this->sequenceNumber + 2, 3.33);
        $this->eventB = new TestCallEvent($this->sequenceNumber + 3, 4.44);
        $this->events = array($this->calledEvent, $this->returnedEvent, $this->eventA, $this->eventB);
        $this->call = new Call($this->events);
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = new TestAssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->duration = $this->endTime - $this->startTime;
        $this->argumentCount = count($this->arguments);

        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = new ThrewEvent($this->exception, $this->sequenceNumber + 1, $this->endTime);
        $this->otherEvents = array($this->eventA, $this->eventB);

        $this->callWithException = new Call(
            array(
                new CalledEvent(
                    $this->reflector,
                    $this->thisValue,
                    $this->arguments,
                    $this->sequenceNumber,
                    $this->startTime
                ),
                new ThrewEvent($this->exception, $this->sequenceNumber + 1, $this->endTime),
            )
        );
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->callNoArguments = new Call(
            array(
                new CalledEvent($this->reflector, $this->thisValue, array(), $this->sequenceNumber, $this->startTime),
                new ReturnedEvent($this->returnValue, $this->sequenceNumber + 1, $this->endTime),
            )
        );
        $this->subjectNoArguments = new CallVerifier(
            $this->callNoArguments,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->earlyCall = new Call(
            array(
                new CalledEvent(
                    $this->reflector,
                    $this->thisValue,
                    $this->arguments,
                    $this->sequenceNumber - 2,
                    $this->startTime
                ),
                new ReturnedEvent($this->returnValue, $this->sequenceNumber - 1, $this->endTime),
            )
        );
        $this->lateCall = new Call(
            array(
                new CalledEvent(
                    $this->reflector,
                    $this->thisValue,
                    $this->arguments,
                    $this->sequenceNumber + 2,
                    $this->startTime
                ),
                new ReturnedEvent($this->returnValue, $this->sequenceNumber + 3, $this->endTime),
            )
        );

        $this->argumentMatchers = $this->matcherFactory->adaptAll($this->arguments);
    }

    public function testConstructor()
    {
        $this->assertSame($this->call, $this->subject->call());
        $this->assertSame($this->duration, $this->subject->duration());
        $this->assertSame($this->argumentCount, $this->subject->argumentCount());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallVerifier($this->call);

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->reflector, $this->subject->reflector());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->startTime, $this->subject->startTime());
        $this->assertSame($this->endTime, $this->subject->endTime());
        $this->assertNull($this->subject->exception());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
    }

    public function testSetEvents()
    {
        $this->events = array($this->calledEvent, $this->eventA, $this->returnedEvent);
        $this->otherEvents = array($this->eventA);
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->events = array($this->calledEvent);
        $this->otherEvents = array();
        $this->subject->setEvents($this->events);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
    }

    public function testAddEvents()
    {
        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array($this->eventA, $this->returnedEvent));
        $this->events = array($this->calledEvent, $this->eventA, $this->returnedEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array($this->eventA, $this->threwEvent));
        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvents(array());
        $this->events = array($this->calledEvent);
        $this->otherEvents = array();

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertNull($this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
    }

    public function testAddEvent()
    {
        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvent($this->eventA);
        $this->subject->addEvent($this->returnedEvent);
        $this->events = array($this->calledEvent, $this->eventA, $this->returnedEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());

        $this->subject->setEvents(array($this->calledEvent));
        $this->subject->addEvent($this->eventA);
        $this->subject->addEvent($this->threwEvent);
        $this->events = array($this->calledEvent, $this->eventA, $this->threwEvent);
        $this->otherEvents = array($this->eventA);

        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->threwEvent, $this->subject->endEvent());
        $this->assertSame($this->otherEvents, $this->subject->otherEvents());
    }

    public function calledWithData()
    {
        //                                    arguments                                                  calledWith calledWithExactly
        return array(
            'Exact arguments'        => array(array('argumentA', 'argumentB', 'argumentC'),              true,      true),
            'First arguments'        => array(array('argumentA', 'argumentB'),                           true,      false),
            'Single argument'        => array(array('argumentA'),                                        true,      false),
            'Last arguments'         => array(array('argumentB', 'argumentC'),                           false,     false),
            'Last argument'          => array(array('argumentC'),                                        false,     false),
            'Extra arguments'        => array(array('argumentA', 'argumentB', 'argumentC', 'argumentD'), false,     false),
            'First argument differs' => array(array('argumentD', 'argumentB', 'argumentC'),              false,     false),
            'Last argument differs'  => array(array('argumentA', 'argumentB', 'argumentD'),              false,     false),
            'Unused argument'        => array(array('argumentD'),                                        false,     false),
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
        $this->assertNull($this->subject->assertCalledWith('argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject
                ->assertCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertNull($this->subject->assertCalledWith('argumentA', 'argumentB'));
        $this->assertNull($this->subject->assertCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1]));
        $this->assertNull($this->subject->assertCalledWith('argumentA'));
        $this->assertNull($this->subject->assertCalledWith($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertCalledWith());
        $this->assertSame(7, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledWithFailure()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'argumentB'>, <'argumentC'>, <any>*
Actual arguments:
    'argumentA', 'argumentB', 'argumentC'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWith('argumentB', 'argumentC');
    }

    public function testAssertCalledWithFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'argumentB'>, <'argumentC'>, <any>*
Actual arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectNoArguments->assertCalledWith('argumentB', 'argumentC');
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
        $this->assertNull($this->subject->assertCalledWithExactly('argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject
                ->assertCalledWithExactly($this->argumentMatchers[0], $this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledWithExactlyFailure()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'argumentA'>, <'argumentB'>
Actual arguments:
    'argumentA', 'argumentB', 'argumentC'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWithExactly('argumentA', 'argumentB');
    }

    public function testAssertCalledWithExactlyFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'argumentA'>, <'argumentB'>
Actual arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectNoArguments->assertCalledWithExactly('argumentA', 'argumentB');
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
        $this->assertNull($this->subject->assertNotCalledWith('argumentB', 'argumentC'));
        $this->assertNull($this->subject->assertNotCalledWith($this->argumentMatchers[1], $this->argumentMatchers[2]));
        $this->assertNull($this->subject->assertNotCalledWith('argumentC'));
        $this->assertNull($this->subject->assertNotCalledWith($this->argumentMatchers[2]));
        $this->assertNull($this->subject->assertNotCalledWith('argumentA', 'argumentB', 'argumentC', 'argumentD'));
        $this->assertNull(
            $this->subject->assertNotCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNotCalledWith('argumentD', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject->assertNotCalledWith(
                $this->matcherFactory->adapt('argumentD'),
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertNotCalledWith('argumentA', 'argumentB', 'argumentD'));
        $this->assertNull(
            $this->subject->assertNotCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNotCalledWith('argumentD'));
        $this->assertNull($this->subject->assertNotCalledWith($this->matcherFactory->adapt('argumentD')));
        $this->assertSame(12, $this->assertionRecorder->successCount());
    }

    public function testAssertNotCalledWithFailure()
    {
        $expected = <<<'EOD'
Expected arguments unlike:
    <'argumentA'>, <'argumentB'>, <any>*
Actual arguments:
    'argumentA', 'argumentB', 'argumentC'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNotCalledWith('argumentA', 'argumentB');
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
        $this->subjectNoArguments->assertNotCalledWith();
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
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentA', 'argumentB'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->argumentMatchers[0], $this->argumentMatchers[1]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentA'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentB', 'argumentC'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->argumentMatchers[1], $this->argumentMatchers[2]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentC'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->argumentMatchers[2]));
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentA', 'argumentB', 'argumentC', 'argumentD'));
        $this->assertNull(
            $this->subject->assertNotCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentD', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject->assertNotCalledWithExactly(
                $this->matcherFactory->adapt('argumentD'),
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentA', 'argumentB', 'argumentD'));
        $this->assertNull(
            $this->subject->assertNotCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNotCalledWithExactly('argumentD'));
        $this->assertNull($this->subject->assertNotCalledWithExactly($this->matcherFactory->adapt('argumentD')));
        $this->assertSame(16, $this->assertionRecorder->successCount());
    }

    public function testAssertNotCalledWithExactlyFailure()
    {
        $expected = <<<'EOD'
Expected arguments unlike:
    <'argumentA'>, <'argumentB'>, <'argumentC'>
Actual arguments:
    'argumentA', 'argumentB', 'argumentC'
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNotCalledWithExactly('argumentA', 'argumentB', 'argumentC');
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
        $this->subjectNoArguments->assertNotCalledWithExactly();
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
        $this->assertFalse($this->subject->returned('anotherValue'));
        $this->assertFalse($this->subject->returned($this->matcherFactory->adapt('anotherValue')));
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
            "Expected return value like <'value'>. Returned 'returnValue'."
        );
        $this->subject->assertReturned('value');
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
