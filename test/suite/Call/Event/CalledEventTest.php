<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

#[AllowDynamicProperties]
class CalledEventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->callback = 'implode';
        $this->parameters = (new ReflectionFunction('implode'))->getParameters();
        $this->parameterNames = ['separator', 'array'];
        $this->arguments = new Arguments(['a', 'b']);
        $this->subject = new CalledEvent(
            $this->sequenceNumber,
            $this->time,
            $this->callback,
            $this->parameters,
            $this->parameterNames,
            $this->arguments
        );

        $this->callFactory = new TestCallFactory();
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->parameters, $this->subject->parameters());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertNull($this->subject->call());
    }

    public function testSetCall()
    {
        $call = $this->callFactory->create();
        $this->subject->setCall($call);

        $this->assertSame($call, $this->subject->call());
    }
}
