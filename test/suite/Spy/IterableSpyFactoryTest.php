<?php

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestIteratorAggregate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Traversable;

class IterableSpyFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->subject = new IterableSpyFactory($this->callEventFactory);
    }

    public function testCreateWithArrayReturn()
    {
        $values = ['a' => 'b', 'c' => 'd'];
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
        $iterableEvents = [
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        ];
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Traversable::class, $spy);
        $this->assertEquals($iterableEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateWithIteratorReturn()
    {
        $values = ['a' => 'b', 'c' => 'd'];
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
        $iterableEvents = [
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        ];
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Traversable::class, $spy);
        $this->assertEquals($iterableEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateWithIteratorAggregateReturn()
    {
        $values = ['a' => 'b', 'c' => 'd'];
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
        $iterableEvents = [
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced('a', 'b'),
            $this->callEventFactory->createProduced('c', 'd'),
        ];
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Traversable::class, $spy);
        $this->assertEquals($iterableEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateFailureInvalidIterable()
    {
        $this->call = $this->callFactory->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported iterable of type NULL.');
        $this->subject->create($this->call, null);
    }

    public function testCreateFailureInvalidIterableObject()
    {
        $this->call = $this->callFactory->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported iterable of type 'stdClass'.");
        $this->subject->create($this->call, (object) []);
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
