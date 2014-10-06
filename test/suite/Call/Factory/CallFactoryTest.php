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
use Eloquent\Phony\Call\Event\GeneratedEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\SentEvent;
use Eloquent\Phony\Call\Event\SentExceptionEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\YieldedEvent;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Spy;
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
        $spy = new Spy();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(array($expected), $spy->recordedCalls());
    }

    public function testRecordDefaults()
    {
        $actual = $this->subject->record();

        $this->assertInstanceOf('Eloquent\Phony\Call\Call', $actual);

        $this->assertInstanceOf('Eloquent\Phony\Call\Event\CalledEvent', $actual->calledEvent());
        $this->assertSame(array(), $actual->calledEvent()->arguments());
        $this->assertInstanceOf('Eloquent\Phony\Call\Event\ReturnedEvent', $actual->responseEvent());
        $this->assertNull($actual->responseEvent()->value());
    }

    public function testCreate()
    {
        $calledEvent = $this->subject->createCalledEvent();
        $returnedEvent = $this->subject->createReturnedEvent();
        $expected = new Call($calledEvent, $returnedEvent);
        $actual = $this->subject->create($calledEvent, $returnedEvent);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateWithGeneratorEvents()
    {
        if (!class_exists('Generator')) {
            $this->markTestSkipped('Requires generator support.');
        }

        $calledEvent = $this->subject->createCalledEvent();
        $generatedEvent = $this->subject->createGeneratedEvent();
        $generatorEvents = array($this->subject->createSentEvent());
        $expected = new Call($calledEvent, $generatedEvent, $generatorEvents);
        $actual = $this->subject->create($calledEvent, $generatedEvent, $generatorEvents);

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

    public function testCreateThrewEvent()
    {
        $expected = new ThrewEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createThrewEvent($this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateGeneratedEvent()
    {
        if (!class_exists('Generator')) {
            $this->markTestSkipped('Requires generator support.');
        }

        $generatorFactory = eval('return function () { return; yield null; };');
        $generator = call_user_func($generatorFactory);
        $expected = new GeneratedEvent(0, 0.0, $generator);
        $actual = $this->subject->createGeneratedEvent($generator);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateYieldedEvent()
    {
        $value = 'x';
        $key = 'y';
        $expected = new YieldedEvent(0, 0.0, $key, $value);
        $actual = $this->subject->createYieldedEvent($key, $value);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateSentEvent()
    {
        $sentValue = 'x';
        $expected = new SentEvent(0, 0.0, $sentValue);
        $actual = $this->subject->createSentEvent($sentValue);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateSentExceptionEvent()
    {
        $expected = new SentExceptionEvent(0, 0.0, $this->exception);
        $actual = $this->subject->createSentExceptionEvent($this->exception);

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
