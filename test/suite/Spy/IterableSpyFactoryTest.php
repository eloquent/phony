<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use ArrayIterator;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestIteratorAggregate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Traversable;

#[AllowDynamicProperties]
class IterableSpyFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->subject = $this->container->iterableSpyFactory;
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;
    }

    public function testCreateWithArrayReturn()
    {
        $values = ['a' => 'b', 'c' => 'd'];
        $iterable = $values;
        $this->call = $this->callFactory->create(
            $this->eventFactory->createCalled(),
            $this->eventFactory->createReturned($iterable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $iterable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->eventFactory->sequencer()->set(0);
        $this->eventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $iterableEvents = [
            $this->eventFactory->createUsed(),
            $this->eventFactory->createProduced('a', 'b'),
            $this->eventFactory->createProduced('c', 'd'),
        ];
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createConsumed();
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
            $this->eventFactory->createCalled(),
            $this->eventFactory->createReturned($iterable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $iterable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->eventFactory->sequencer()->set(0);
        $this->eventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $iterableEvents = [
            $this->eventFactory->createUsed(),
            $this->eventFactory->createProduced('a', 'b'),
            $this->eventFactory->createProduced('c', 'd'),
        ];
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Traversable::class, $spy);
        $this->assertEquals($iterableEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($values, $actual);
    }

    public function testCreateWithIteratorAggregateReturn()
    {
        $values = ['a' => 'b', 'c' => 'd'];
        $iterable = new TestIteratorAggregate(new ArrayIterator($values));
        $this->call = $this->callFactory->create(
            $this->eventFactory->createCalled(),
            $this->eventFactory->createReturned($iterable)
        );
        $this->callFactory->reset();
        $spy = $this->subject->create($this->call, $iterable);
        iterator_to_array($spy);
        $actual = iterator_to_array($spy);
        $this->eventFactory->sequencer()->set(0);
        $this->eventFactory->clock()->setTime(1.0);
        $this->callFactory->reset();
        $iterableEvents = [
            $this->eventFactory->createUsed(),
            $this->eventFactory->createProduced('a', 'b'),
            $this->eventFactory->createProduced('c', 'd'),
        ];
        foreach ($iterableEvents as $iterableEvent) {
            $iterableEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createConsumed();
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
}
