<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use Eloquent\Phony\Call\Event\ReturnedEvent;
use PHPUnit_Framework_TestCase;

class EventCollectionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->eventA = new ReturnedEvent(0, 0.0);
        $this->eventB = new ReturnedEvent(1, 1.0);
        $this->eventC = new ReturnedEvent(2, 2.0);
        $this->events = array($this->eventA, $this->eventB, $this->eventC);
        $this->subject = new EventCollection($this->events);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->eventA, $this->subject->firstEvent());
        $this->assertSame($this->eventC, $this->subject->lastEvent());
        $this->assertSame(3, count($this->subject));
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EventCollection();

        $this->assertFalse($this->subject->hasEvents());
        $this->assertSame(array(), $this->subject->events());
        $this->assertNull($this->subject->firstEvent());
        $this->assertNull($this->subject->lastEvent());
        $this->assertSame(0, count($this->subject));
    }

    public function testIteration()
    {
        $this->assertSame($this->events, iterator_to_array($this->subject));
    }
}
