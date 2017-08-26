<?php

namespace Eloquent\Phony\Spy;

use ArrayObject;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit\Framework\TestCase;

class TraversableSpyTest extends TestCase
{
    protected function setUp()
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
        $this->assertFalse(isset($this->subject['e']));

        $this->subject['e'] = 'f';

        $this->assertTrue(isset($this->subject['e']));
        $this->assertSame('f', $this->subject['e']);

        unset($this->subject['e']);

        $this->assertFalse(isset($this->subject['e']));
    }

    public function testCountable()
    {
        $this->assertSame(2, count($this->subject));

        $this->subject['e'] = 'f';

        $this->assertSame(3, count($this->subject));
    }
}
