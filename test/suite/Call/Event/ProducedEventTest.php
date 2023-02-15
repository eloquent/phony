<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use AllowDynamicProperties;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class ProducedEventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->key = 'x';
        $this->value = 'y';
        $this->subject = new ProducedEvent($this->sequenceNumber, $this->time, $this->key, $this->value);

        $this->callFactory = new TestCallFactory();
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->key, $this->subject->key());
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
