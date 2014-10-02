<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class CalledEventTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->reflector = new ReflectionMethod(__METHOD__);
        $this->thisValue = $this;
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->subject = new CalledEvent(
            $this->reflector,
            $this->thisValue,
            $this->arguments,
            $this->sequenceNumber,
            $this->time
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->reflector, $this->subject->reflector());
        $this->assertSame($this->thisValue, $this->subject->thisValue());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
    }
}
