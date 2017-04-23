<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestIteratorAggregate;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class IterableSpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->subject = new IterableSpyFactory($this->callEventFactory);
    }

    public function testCreateWithArrayReturn()
    {
        $values = array('a' => 'b', 'c' => 'd');
        $iterable = $values;
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($iterable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $iterable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->callEventFactory->sequencer()->set(0);
        $this->callEventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $iterableEvents = array(
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        );
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Traversable', $spy);
        $this->assertEquals($iterableEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateWithIteratorReturn()
    {
        $values = array('a' => 'b', 'c' => 'd');
        $iterable = new ArrayIterator($values);
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($iterable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $iterable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->callEventFactory->sequencer()->set(0);
        $this->callEventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $iterableEvents = array(
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        );
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Traversable', $spy);
        $this->assertEquals($iterableEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateWithIteratorAggregateReturn()
    {
        $values = array('a' => 'b', 'c' => 'd');
        $iterable = new TestIteratorAggregate($values);
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($iterable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $iterable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->callEventFactory->sequencer()->set(0);
        $this->callEventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $iterableEvents = array(
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        );
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Traversable', $spy);
        $this->assertEquals($iterableEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateFailureInvalidIterable()
    {
        $this->call = $this->callFactory->create();

        $this->setExpectedException('InvalidArgumentException', 'Unsupported iterable of type NULL.');
        $this->subject->create($this->call, null);
    }

    public function testCreateFailureInvalidIterableObject()
    {
        $this->call = $this->callFactory->create();

        $this->setExpectedException('InvalidArgumentException', "Unsupported iterable of type 'stdClass'.");
        $this->subject->create($this->call, (object) array());
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
