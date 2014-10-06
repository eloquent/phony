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

class YieldedEventTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->key = 'x';
        $this->value = 'y';
        $this->subject = new YieldedEvent($this->sequenceNumber, $this->time, $this->key, $this->value);
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->key, $this->subject->key());
        $this->assertSame($this->value, $this->subject->value());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new YieldedEvent($this->sequenceNumber, $this->time, $this->key);

        $this->assertNull($this->subject->value());
    }
}
