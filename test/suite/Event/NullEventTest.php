<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use Eloquent\Phony\Call\Argument\Arguments;
use PHPUnit_Framework_TestCase;

class NullEventTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new NullEvent();
    }

    public function testConstructor()
    {
        $this->assertSame(-1, $this->subject->sequenceNumber());
        $this->assertEquals(-1.0, $this->subject->time());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertFalse($this->subject->hasCalls());
        $this->assertSame(1, $this->subject->eventCount());
        $this->assertSame(1, count($this->subject));
        $this->assertSame(0, $this->subject->callCount());
        $this->assertSame(array($this->subject), $this->subject->allEvents());
        $this->assertSame(array($this->subject), iterator_to_array($this->subject));
        $this->assertSame(array(), $this->subject->allCalls());
    }

    public function testEventAt()
    {
        $this->assertSame($this->subject, $this->subject->eventAt());
        $this->assertSame($this->subject, $this->subject->eventAt(0));
        $this->assertSame($this->subject, $this->subject->eventAt(-1));
    }

    public function testEventAtFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->eventAt(1);
    }

    public function testCallAtFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->callAt();
    }

    public function testArgumentsFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->arguments();
    }

    public function testArgumentFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->argument();
    }
}
