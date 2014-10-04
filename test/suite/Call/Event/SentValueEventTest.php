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

class SentValueEventTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->sentValue = 'x';
        $this->subject = new SentValueEvent($this->sequenceNumber, $this->time, $this->sentValue);
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->sentValue, $this->subject->sentValue());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SentValueEvent($this->sequenceNumber, $this->time);

        $this->assertNull($this->subject->sentValue());
    }
}
