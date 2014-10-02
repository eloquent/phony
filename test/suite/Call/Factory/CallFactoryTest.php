<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestClock;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class CallFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sequencer = new Sequencer();
        $this->clock = new TestClock();
        $this->subject = new CallFactory($this->sequencer, $this->clock);

        $this->exception = new RuntimeException('You done goofed.');
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequencer, $this->subject->sequencer());
        $this->assertSame($this->clock, $this->subject->clock());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallFactory();

        $this->assertSame(Sequencer::instance(), $this->subject->sequencer());
        $this->assertSame(SystemClock::instance(), $this->subject->clock());
    }

    public function testRecord()
    {
        $callback = 'implode';
        $arguments = array(array('a', 'b'));
        $returnValue = 'ab';
        $expected = $this->subject->create(
            array(
                $this->subject->createCalledEvent($callback, $arguments),
                $this->subject->createReturnedEvent($returnValue),
            )
        );
        $this->sequencer->reset();
        $this->clock->reset();
        $actual = $this->subject->record($callback, $arguments);

        $this->assertEquals($expected, $actual);
    }

    public function testRecordDefaults()
    {
        $expected = $this->subject->create();
        $this->sequencer->reset();
        $this->clock->reset();
        $actual = $this->subject->record();

        $this->assertEquals($expected, $actual);
    }

    public function testCreate()
    {
        $events = array($this->subject->createCalledEvent());
        $expected = new Call($events);
        $actual = $this->subject->create($events);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateDefaults()
    {
        $events = array($this->subject->createCalledEvent(), $this->subject->createReturnedEvent());
        $expected = new Call($events);
        $this->sequencer->reset();
        $this->clock->reset();
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCalledEvent()
    {
        $callback = 'implode';
        $arguments = array('a', 'b');
        $expected = new CalledEvent(0, 0.0, $callback, $arguments);
        $actual = $this->subject->createCalledEvent($callback, $arguments);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateEndEventWithNoException()
    {
        $returnValue = 'x';
        $expected = new ReturnedEvent(0, 0.0, $returnValue);
        $actual = $this->subject->createEndEvent($returnValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateEndEventWithException()
    {
        $expected = new ThrewEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createEndEvent(null, $this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateEndEventDefaults()
    {
        $expected = new ReturnedEvent(0, 0.0);
        $actual = $this->subject->createEndEvent();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateReturnedEvent()
    {
        $returnValue = 'x';
        $expected = new ReturnedEvent(0, 0.0, $returnValue);
        $actual = $this->subject->createReturnedEvent($returnValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateThrewEvent()
    {
        $expected = new ThrewEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createThrewEvent($this->exception);

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
