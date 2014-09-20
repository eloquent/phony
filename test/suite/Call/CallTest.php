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

use PHPUnit_Framework_TestCase;
use RuntimeException;

class CallTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
        $this->returnValue = 'returnValue';
        $this->sequenceNumber = 111;
        $this->startTime = 1.11;
        $this->endTime = 2.22;
        $this->exception = new RuntimeException();
        $this->thisValue = (object) array();
        $this->subject = new Call(
            $this->arguments,
            $this->returnValue,
            $this->sequenceNumber,
            $this->startTime,
            $this->endTime,
            $this->exception,
            $this->thisValue
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->startTime, $this->subject->startTime());
        $this->assertSame($this->endTime, $this->subject->endTime());
        $this->assertSame($this->exception, $this->subject->exception());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Call(
            $this->arguments,
            $this->returnValue,
            $this->sequenceNumber,
            $this->startTime,
            $this->endTime
        );

        $this->assertNull($this->subject->exception());
        $this->assertNull($this->subject->thisValue());
    }
}
