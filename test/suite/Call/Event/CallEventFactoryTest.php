<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClock;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[AllowDynamicProperties]
class CallEventFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new FacadeContainer(clock: new TestClock());
        $this->subject = $container->eventFactory;

        $this->exception = new RuntimeException('You done goofed.');
    }

    public function testCreateCalled()
    {
        $callback = 'implode';
        $arguments = Arguments::create('a', 'b');
        $expected = new CalledEvent(0, 0.0, $callback, $arguments);
        $actual = $this->subject->createCalled($callback, $arguments);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateReturned()
    {
        $value = 'x';
        $expected = new ReturnedEvent(0, 0.0, $value);
        $actual = $this->subject->createReturned($value);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateThrew()
    {
        $expected = new ThrewEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createThrew($this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateUsed()
    {
        $expected = new UsedEvent(0, 0.0);
        $actual = $this->subject->createUsed();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateProduced()
    {
        $key = 'x';
        $value = 'y';
        $expected = new ProducedEvent(0, 0.0, $key, $value);
        $actual = $this->subject->createProduced($key, $value);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateReceived()
    {
        $value = 'x';
        $expected = new ReceivedEvent(0, 0.0, $value);
        $actual = $this->subject->createReceived($value);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateReceivedException()
    {
        $expected = new ReceivedExceptionEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createReceivedException($this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateConsumed()
    {
        $expected = new ConsumedEvent(0, 0.0);
        $actual = $this->subject->createConsumed();

        $this->assertEquals($expected, $actual);
    }
}
