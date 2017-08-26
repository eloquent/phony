<?php

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit\Framework\TestCase;

class ReceivedExceptionEventTest extends TestCase
{
    protected function setUp()
    {
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->exception = new Exception();
        $this->subject = new ReceivedExceptionEvent($this->sequenceNumber, $this->time, $this->exception);

        $this->callFactory = new TestCallFactory();
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->exception, $this->subject->exception());
        $this->assertNull($this->subject->call());
    }

    public function testSetCall()
    {
        $call = $this->callFactory->create();
        $this->subject->setCall($call);

        $this->assertSame($call, $this->subject->call());
    }
}
