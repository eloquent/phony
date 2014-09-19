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

use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use stdClass;

class CallVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
        $this->returnValue = 'returnValue';
        $this->thisValue = new stdClass;
        $this->sequenceNumber = 111;
        $this->startTime = 1.11;
        $this->endTime = 2.22;
        $this->exception = new RuntimeException('You done goofed.');
        $this->call = new Call(
            $this->arguments,
            $this->returnValue,
            $this->thisValue,
            $this->sequenceNumber,
            $this->startTime,
            $this->endTime,
            $this->exception
        );
        $this->matcherFactory = new MatcherFactory;
        $this->subject = new CallVerifier($this->call, $this->matcherFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->call, $this->subject->call());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallVerifier($this->call);

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->startTime, $this->subject->startTime());
        $this->assertSame($this->endTime, $this->subject->endTime());
        $this->assertSame($this->exception, $this->subject->exception());
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
        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'calledWith'), $arguments));
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'calledWithExactly'), $arguments)
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNotCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->assertSame(!$calledWith, call_user_func_array(array($this->subject, 'notCalledWith'), $arguments));
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNotCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject, 'notCalledWithExactly'), $arguments)
        );
    }

    public function testThrew()
    {
        $this->assertTrue($this->subject->threw());
        $this->assertTrue($this->subject->threw('Exception'));
        $this->assertTrue($this->subject->threw('RuntimeException'));
        $this->assertTrue($this->subject->threw($this->exception));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception));
        $this->assertFalse($this->subject->threw(new RuntimeException));
    }

    public function testCalledOn()
    {
        $this->assertTrue($this->subject->calledOn($this->thisValue));
        $this->assertFalse($this->subject->calledOn(new stdClass));
    }
}
