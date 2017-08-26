<?php

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit\Framework\TestCase;

class ReceivedEventTest extends TestCase
{
    protected function setUp()
    {
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->value = 'x';
        $this->subject = new ReceivedEvent($this->sequenceNumber, $this->time, $this->value);

        $this->callFactory = new TestCallFactory();
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->value, $this->subject->value());
        $this->assertNull($this->subject->call());
    }

    public function testSetCall()
    {
        $call = $this->callFactory->create();
        $this->subject->setCall($call);

        $this->assertSame($call, $this->subject->call());
    }
}
