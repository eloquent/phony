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

use Closure;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestClock;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;

class CallFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sequencer = new Sequencer();
        $this->clock = new TestClock();
        $this->subject = new CallFactory($this->sequencer, $this->clock);

        $this->reflector = new ReflectionMethod(__METHOD__);
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
        $callback = function () {
            return '= ' . implode(' + ', func_get_args());
        };
        $reflector = new ReflectionFunction($callback);
        $expected = new Call(
            array(
                new CalledEvent(
                    $reflector,
                    $this->closureThisValue($callback),
                    array('argumentA', 'argumentB'),
                    0,
                    0.123
                ),
                new ReturnedEvent('= argumentA + argumentB', 1, 1.123),
            )
        );
        $actual = $this->subject->record($callback, array('argumentA', 'argumentB'));

        $this->assertEquals($expected, $actual);
    }

    public function testRecordDefaults()
    {
        $actual = $this->subject->record();

        $this->assertInstanceOf('Eloquent\Phony\Call\Call', $actual);

        $events = $actual->events();

        $this->assertSame(2, count($events));
        $this->assertInstanceOf('Eloquent\Phony\Call\Event\CalledEvent', $events[0]);
        $this->assertInstanceOf('ReflectionFunction', $events[0]->reflector());
        $this->assertSame(array(), $events[0]->arguments());
        $this->assertSame(0, $events[0]->sequenceNumber());
        $this->assertEquals(0.123, $events[0]->time());
        $this->assertEquals(new ReturnedEvent(null, 1, 1.123), $events[1]);
    }

    public function testCreate()
    {
        $events = array(
            new CalledEvent($this->reflector, $this, array('argumentA', 'argumentB'), 0, 0.123),
            new ReturnedEvent(null, 1, 1.123),
        );
        $expected = new Call($events);
        $actual = $this->subject->create($events);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCalledEventWithMethod()
    {
        $reflector = $this->reflector;
        $expected = new CalledEvent($reflector, $this, array('argumentA', 'argumentB'), 0, 0.123);
        $actual = $this->subject->createCalledEvent(array($this, 'setUp'), array('argumentA', 'argumentB'));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCalledEventWithStaticMethod()
    {
        $reflector = new ReflectionMethod('Eloquent\Phony\Call\Factory\CallFactory', 'instance');
        $expected = new CalledEvent($reflector, null, array('argumentA', 'argumentB'), 0, 0.123);
        $actual = $this->subject
            ->createCalledEvent('Eloquent\Phony\Call\Factory\CallFactory::instance', array('argumentA', 'argumentB'));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCalledEventWithFunction()
    {
        $reflector = new ReflectionFunction('function_exists');
        $expected = new CalledEvent($reflector, null, array('argumentA', 'argumentB'), 0, 0.123);
        $actual = $this->subject->createCalledEvent('function_exists', array('argumentA', 'argumentB'));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCalledEventWithClosure()
    {
        $callback = function () {};
        $reflector = new ReflectionFunction($callback);
        $expected =
            new CalledEvent($reflector, $this->closureThisValue($callback), array('argumentA', 'argumentB'), 0, 0.123);
        $actual = $this->subject->createCalledEvent($callback, array('argumentA', 'argumentB'));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCalledEventDefaults()
    {
        $actual = $this->subject->createCalledEvent();

        $this->assertInstanceOf('Eloquent\Phony\Call\Event\CalledEvent', $actual);
        $this->assertInstanceOf('ReflectionFunction', $actual->reflector());
        $this->assertSame(array(), $actual->arguments());
        $this->assertSame(0, $actual->sequenceNumber());
        $this->assertEquals(0.123, $actual->time());
    }

    public function testCreateCalledEventFailureInvalidCallback()
    {
        $this->setExpectedException('InvalidArgumentException', "Unsupported callback of type 'integer'.");
        $this->subject->createCalledEvent(111);
    }

    public function testCreateEndEvent()
    {
        $expected = new ReturnedEvent('returnValue', 0, 0.123);
        $actual = $this->subject->createEndEvent('returnValue', null);

        $this->assertEquals($expected, $actual);

        $expected = new ThrewEvent($this->exception, 1, 1.123);
        $actual = $this->subject->createEndEvent(null, $this->exception);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateReturnedEvent()
    {
        $expected = new ReturnedEvent('returnValue', 0, 0.123);
        $actual = $this->subject->createReturnedEvent('returnValue');

        $this->assertEquals($expected, $actual);
    }

    public function testCreateThrewEvent()
    {
        $expected = new ThrewEvent($this->exception, 0, 0.123);
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

    protected function closureThisValue(Closure $closure)
    {
        $reflectorReflector = new ReflectionClass('ReflectionFunction');
        if (!$reflectorReflector->hasMethod('getClosureThis')) {
            return null;
        }

        $reflector = new ReflectionFunction($closure);

        return $reflector->getClosureThis();
    }
}
