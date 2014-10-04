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
use Eloquent\Phony\Call\Event\SentValueEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Invocation\Invoker;
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
        $this->invoker = new Invoker();
        $this->subject = new CallFactory($this->sequencer, $this->clock, $this->invoker);

        $this->exception = new RuntimeException('You done goofed.');
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequencer, $this->subject->sequencer());
        $this->assertSame($this->clock, $this->subject->clock());
        $this->assertSame($this->invoker, $this->subject->invoker());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallFactory();

        $this->assertSame(Sequencer::instance(), $this->subject->sequencer());
        $this->assertSame(SystemClock::instance(), $this->subject->clock());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
    }

    public function testRecord()
    {
        $callback = 'implode';
        $arguments = array(array('a', 'b'));
        $returnValue = 'ab';
        $expected = $this->subject->create(
            $this->subject->createCalledEvent($callback, $arguments),
            $this->subject->createReturnedEvent($returnValue)
        );
        $this->sequencer->reset();
        $this->clock->reset();
        $actual = $this->subject->record($callback, $arguments);

        $this->assertEquals($expected, $actual);
    }

    public function testRecordDefaults()
    {
        $actual = $this->subject->record();

        $this->assertInstanceOf('Eloquent\Phony\Call\Call', $actual);

        $this->assertInstanceOf('Eloquent\Phony\Call\Event\CalledEvent', $actual->calledEvent());
        $this->assertSame(array(), $actual->calledEvent()->arguments());
        $this->assertInstanceOf('Eloquent\Phony\Call\Event\ReturnedEvent', $actual->responseEvent());
        $this->assertNull($actual->responseEvent()->returnValue());
    }

    public function testCreate()
    {
        $calledEvent = $this->subject->createCalledEvent();
        $returnedEvent = $this->subject->createReturnedEvent();
        $generatorEvents = array($this->subject->createSentValueEvent());
        $expected = new Call($calledEvent, $returnedEvent, $generatorEvents);
        $actual = $this->subject->create($calledEvent, $returnedEvent, $generatorEvents);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateDefaults()
    {
        $expected = new Call($this->subject->createCalledEvent());
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

    public function testCreateResponseEventWithNoException()
    {
        $returnValue = 'x';
        $expected = new ReturnedEvent(0, 0.0, $returnValue);
        $actual = $this->subject->createResponseEvent($returnValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateResponseEventWithException()
    {
        $expected = new ThrewEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createResponseEvent(null, $this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateResponseEventDefaults()
    {
        $expected = new ReturnedEvent(0, 0.0);
        $actual = $this->subject->createResponseEvent();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateReturnedEvent()
    {
        $returnValue = 'x';
        $expected = new ReturnedEvent(0, 0.0, $returnValue);
        $actual = $this->subject->createReturnedEvent($returnValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateSentValueEvent()
    {
        $sentValue = 'x';
        $expected = new SentValueEvent(0, 0.0, $sentValue);
        $actual = $this->subject->createSentValueEvent($sentValue);

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
