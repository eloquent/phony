<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use ArrayObject;
use Eloquent\Phony\Spy\Exception\NonArrayAccessTraversableException;
use Eloquent\Phony\Spy\Exception\NonCountableTraversableException;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestIteratorAggregate;
use Eloquent\Phony\Test\WithDynamicProperties;
use EmptyIterator;
use PHPUnit\Framework\TestCase;

class TraversableSpyTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->array = ['a' => 'b', 'c' => 'd'];
        $this->value = new ArrayObject($this->array);
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($this->value)
        );
        $this->subject = new TraversableSpy($this->call, $this->value, $this->callEventFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->value, $this->subject->iterable());
    }

    public function testIterator()
    {
        $this->assertSame($this->array, iterator_to_array($this->subject));
        $this->assertSame($this->array, iterator_to_array($this->subject));
    }

    public function testArrayAccess()
    {
        $this->assertSame('b', $this->subject['a']);
        $this->assertSame('d', $this->subject['c']);
        $this->assertArrayNotHasKey('e', $this->subject);

        $this->subject['e'] = 'f';

        $this->assertArrayHasKey('e', $this->subject);
        $this->assertSame('f', $this->subject['e']);

        unset($this->subject['e']);

        $this->assertArrayNotHasKey('e', $this->subject);
    }

    public function testArrayAccessOffsetExistsWithNonArrayAccess()
    {
        $traversable = new EmptyIterator();
        $subject = new TraversableSpy($this->call, $traversable, $this->callEventFactory);

        $this->expectException(NonArrayAccessTraversableException::class);
        isset($subject[0]);
    }

    public function testArrayAccessOffsetGetWithNonArrayAccess()
    {
        $traversable = new EmptyIterator();
        $subject = new TraversableSpy($this->call, $traversable, $this->callEventFactory);

        $this->expectException(NonArrayAccessTraversableException::class);
        $subject[0];
    }

    public function testArrayAccessOffsetSetWithNonArrayAccess()
    {
        $traversable = new EmptyIterator();
        $subject = new TraversableSpy($this->call, $traversable, $this->callEventFactory);

        $this->expectException(NonArrayAccessTraversableException::class);
        $subject[0] = 0;
    }

    public function testArrayAccessOffsetUnsetWithNonArrayAccess()
    {
        $traversable = new EmptyIterator();
        $subject = new TraversableSpy($this->call, $traversable, $this->callEventFactory);

        $this->expectException(NonArrayAccessTraversableException::class);
        unset($subject[0]);
    }

    public function testCountable()
    {
        $this->assertCount(2, $this->subject);

        $this->subject['e'] = 'f';

        $this->assertCount(3, $this->subject);
    }

    public function testCountableWithNonCountable()
    {
        $traversable = new EmptyIterator();
        $subject = new TraversableSpy($this->call, $traversable, $this->callEventFactory);

        $this->expectException(NonCountableTraversableException::class);
        count($subject);
    }

    public function testNestedIteratorAggregateUnwrapping()
    {
        $traversable = new TestIteratorAggregate(new TestIteratorAggregate(new ArrayObject($this->array)));
        $subject = new TraversableSpy($this->call, $traversable, $this->callEventFactory);

        $this->assertSame($this->array, iterator_to_array($subject));
    }
}
