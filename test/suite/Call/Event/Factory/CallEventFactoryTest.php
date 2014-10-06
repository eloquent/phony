<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event\Factory;

use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\SentEvent;
use Eloquent\Phony\Call\Event\SentExceptionEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\YieldedEvent;
use Eloquent\Phony\Clock\SystemClock;
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

    public function testConstructor()
    {
        $this->assertSame($this->sequencer, $this->subject->sequencer());
        $this->assertSame($this->clock, $this->subject->clock());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallEventFactory();

        $this->assertSame(Sequencer::instance(), $this->subject->sequencer());
        $this->assertSame(SystemClock::instance(), $this->subject->clock());
    }

    public function testCreateCalled()
    {
        $callback = 'implode';
        $arguments = array('a', 'b');
        $expected = new CalledEvent(0, 0.0, $callback, $arguments);
        $actual = $this->subject->createCalled($callback, $arguments);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateResponseWithNoException()
    {
        $returnValue = 'x';
        $expected = new ReturnedEvent(0, 0.0, $returnValue);
        $actual = $this->subject->createResponse($returnValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateResponseWithException()
    {
        $expected = new ThrewEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createResponse(null, $this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateResponseDefaults()
    {
        $expected = new ReturnedEvent(0, 0.0);
        $actual = $this->subject->createResponse();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateReturned()
    {
        $returnValue = 'x';
        $expected = new ReturnedEvent(0, 0.0, $returnValue);
        $actual = $this->subject->createReturned($returnValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateThrew()
    {
        $expected = new ThrewEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createThrew($this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateYielded()
    {
        $value = 'x';
        $key = 'y';
        $expected = new YieldedEvent(0, 0.0, $key, $value);
        $actual = $this->subject->createYielded($key, $value);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateSent()
    {
        $sentValue = 'x';
        $expected = new SentEvent(0, 0.0, $sentValue);
        $actual = $this->subject->createSent($sentValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateSentException()
    {
        $expected = new SentExceptionEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createSentException($this->exception);

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
