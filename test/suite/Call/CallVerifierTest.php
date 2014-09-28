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
use Eloquent\Phony\Integration\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestAssertionRecorder;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use SebastianBergmann\Exporter\Exporter;

class CallVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
        $this->argumentCount = count($this->arguments);
        $this->returnValue = 'returnValue';
        $this->sequenceNumber = 111;
        $this->startTime = 1.11;
        $this->endTime = 2.22;
        $this->duration = $this->endTime - $this->startTime;
        $this->exception = new RuntimeException('You done goofed.');
        $this->thisValue = (object) array();
        $this->call = new Call(
            $this->arguments,
            $this->returnValue,
            $this->sequenceNumber,
            $this->startTime,
            $this->endTime,
            $this->exception,
            $this->thisValue
        );
        $this->matcherFactory = new MatcherFactory(array(new PhpunitMatcherDriver()));
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = new TestAssertionRecorder();
        $this->exporter = new Exporter();
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->exporter
        );

        $this->callNoException = new Call(
            $this->arguments,
            $this->returnValue,
            $this->sequenceNumber,
            $this->startTime,
            $this->endTime
        );
        $this->subjectNoException = new CallVerifier(
            $this->callNoException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->exporter
        );

        $this->earlyCall = new Call(
            $this->arguments,
            $this->returnValue,
            $this->sequenceNumber - 1,
            $this->startTime,
            $this->endTime
        );
        $this->lateCall = new Call(
            $this->arguments,
            $this->returnValue,
            $this->sequenceNumber + 1,
            $this->startTime,
            $this->endTime
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->call, $this->subject->call());
        $this->assertSame($this->duration, $this->subject->duration());
        $this->assertSame($this->argumentCount, $this->subject->argumentCount());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->exporter, $this->subject->exporter());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallVerifier($this->call);

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertEquals($this->exporter, $this->subject->exporter());
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->startTime, $this->subject->startTime());
        $this->assertSame($this->endTime, $this->subject->endTime());
        $this->assertSame($this->exception, $this->subject->exception());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
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

    public function testCalledBefore()
    {
        $this->assertTrue($this->subject->calledBefore($this->lateCall));
        $this->assertFalse($this->subject->calledBefore($this->earlyCall));
    }

    public function testCalledAfter()
    {
        $this->assertTrue($this->subject->calledAfter($this->earlyCall));
        $this->assertFalse($this->subject->calledAfter($this->lateCall));
    }

    public function testCalledOn()
    {
        $this->assertTrue($this->subject->calledOn($this->thisValue));
        $this->assertFalse($this->subject->calledOn((object) array('property' => 'value')));
    }

    public function testAssertCalledOn()
    {
        $this->assertNull($this->subject->assertCalledOn($this->thisValue));
        $this->assertSame(1, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledOnFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "The call was not made on the expected object."
        );
        $this->subject->assertCalledOn((object) array());
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
            "The return value did not match <is equal to <string:value>>. Actual return value was 'returnValue'."
        );
        $this->subject->assertReturned('value');
    }

    public function testThrew()
    {
        $this->assertTrue($this->subject->threw());
        $this->assertTrue($this->subject->threw('Exception'));
        $this->assertTrue($this->subject->threw('RuntimeException'));
        $this->assertTrue($this->subject->threw($this->exception));
        $this->assertTrue($this->subject->threw($this->identicalTo($this->exception)));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));
        $this->assertFalse($this->subject->threw($this->identicalTo(new RuntimeException('You done goofed.'))));
        $this->assertFalse($this->subject->threw($this->isNull()));
        $this->assertFalse($this->subject->threw(111));

        $this->assertFalse($this->subjectNoException->threw());
        $this->assertFalse($this->subjectNoException->threw('Exception'));
        $this->assertFalse($this->subjectNoException->threw('RuntimeException'));
        $this->assertFalse($this->subjectNoException->threw($this->exception));
        $this->assertFalse($this->subjectNoException->threw($this->identicalTo($this->exception)));
        $this->assertFalse($this->subjectNoException->threw('InvalidArgumentException'));
        $this->assertFalse($this->subjectNoException->threw(new Exception()));
        $this->assertFalse($this->subjectNoException->threw(new RuntimeException()));
        $this->assertFalse($this->subjectNoException->threw($this->identicalTo(new RuntimeException('You done goofed.'))));
        $this->assertTrue($this->subjectNoException->threw($this->isNull()));
        $this->assertFalse($this->subjectNoException->threw(111));
    }

    public function testAssertThrew()
    {
        $this->assertNull($this->subject->assertThrew());
        $this->assertNull($this->subject->assertThrew('Exception'));
        $this->assertNull($this->subject->assertThrew('RuntimeException'));
        $this->assertNull($this->subject->assertThrew($this->exception));
        $this->assertNull($this->subject->assertThrew($this->identicalTo($this->exception)));
        $this->assertSame(5, $this->assertionRecorder->successCount());
    }

    public function testAssertThrewFailureExpectingAnyNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected an exception, but no exception was thrown."
        );
        $this->subjectNoException->assertThrew();
    }

    public function testAssertThrewFailureTypeMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected an exception of type 'InvalidArgumentException'. " .
                "Actual exception was RuntimeException('You done goofed.')."
        );
        $this->subject->assertThrew('InvalidArgumentException');
    }

    public function testAssertThrewFailureExpectingTypeNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected an exception of type 'InvalidArgumentException', but no exception was thrown."
        );
        $this->subjectNoException->assertThrew('InvalidArgumentException');
    }

    public function testAssertThrewFailureExceptionMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected an exception equal to RuntimeException(''). " .
                "Actual exception was RuntimeException('You done goofed.')."
        );
        $this->subject->assertThrew(new RuntimeException());
    }

    public function testAssertThrewFailureExpectingExceptionNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected an exception equal to RuntimeException(''), but no exception was thrown."
        );
        $this->subjectNoException->assertThrew(new RuntimeException());
    }

    public function testAssertThrewFailureMatcherMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected an exception matching <is identical to an object of class \"RuntimeException\">. " .
                "Actual exception was RuntimeException('You done goofed.')."
        );
        $this->subject->assertThrew($this->identicalTo(new RuntimeException('You done goofed.')));
    }

    public function testAssertThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Unable to match exceptions against 111."
        );
        $this->subject->assertThrew(111);
    }
}
