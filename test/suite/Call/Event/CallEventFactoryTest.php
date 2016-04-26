<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestClock;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class CallEventFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sequencer = new Sequencer();
        $this->clock = new TestClock();
        $this->subject = new CallEventFactory($this->sequencer, $this->clock);

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

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
