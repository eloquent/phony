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

use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit\Framework\TestCase;

class ArraySpyTest extends TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->array = array('a' => 'b', 'c' => 'd');
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
