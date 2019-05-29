<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit\Framework\TestCase;

class ArraySpyTest extends TestCase
{
    protected function setUp(): void
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->array = ['a' => 'b', 'c' => 'd'];
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($this->array)
        );
        $this->subject = new ArraySpy($this->call, $this->array, $this->callEventFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->array, $this->subject->iterable());
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

    public function testCountable()
    {
        $this->assertCount(2, $this->subject);

        $this->subject['e'] = 'f';

        $this->assertCount(3, $this->subject);
    }
}
