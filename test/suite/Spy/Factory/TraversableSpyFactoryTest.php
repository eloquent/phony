<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use ArrayIterator;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestIteratorAggregate;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class TraversableSpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->subject = new TraversableSpyFactory($this->callEventFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callEventFactory, $this->subject->callEventFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new TraversableSpyFactory();

        $this->assertSame(CallEventFactory::instance(), $this->subject->callEventFactory());
    }

    public function testCreateWithArrayReturn()
    {
        $values = array('a' => 'b', 'c' => 'd');
        $traversable = $values;
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($traversable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $traversable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->callEventFactory->sequencer()->set(0);
        $this->callEventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $traversableEvents = array(
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        );
        foreach ($traversableEvents as $traversableEvent) {
            $traversableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Traversable', $spy);
        $this->assertEquals($traversableEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateWithIteratorReturn()
    {
        $values = array('a' => 'b', 'c' => 'd');
        $traversable = new ArrayIterator($values);
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($traversable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $traversable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->callEventFactory->sequencer()->set(0);
        $this->callEventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $traversableEvents = array(
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        );
        foreach ($traversableEvents as $traversableEvent) {
            $traversableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Traversable', $spy);
        $this->assertEquals($traversableEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateWithIteratorAggregateReturn()
    {
        $values = array('a' => 'b', 'c' => 'd');
        $traversable = new TestIteratorAggregate($values);
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($traversable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $traversable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->callEventFactory->sequencer()->set(0);
        $this->callEventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $traversableEvents = array(
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        );
        foreach ($traversableEvents as $traversableEvent) {
            $traversableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Traversable', $spy);
        $this->assertEquals($traversableEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateFailureInvalidTraversable()
    {
        $this->call = $this->callFactory->create();

        $this->setExpectedException('InvalidArgumentException', 'Unsupported traversable of type NULL.');
        $this->subject->create($this->call, null);
    }

    public function testCreateFailureInvalidTraversableObject()
    {
        $this->call = $this->callFactory->create();

        $this->setExpectedException('InvalidArgumentException', "Unsupported traversable of type 'stdClass'.");
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
