<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;

class IteratorSpyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->values = array('a' => 'b', 'c' => 'd');
        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned($this->values)
        );
        $this->iterator = new ArrayIterator($this->values);
        $this->subject = new IteratorSpy($this->call, $this->iterator, $this->callEventFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->call, $this->subject->call());
        $this->assertSame($this->iterator, $this->subject->iterator());
        $this->assertSame($this->callEventFactory, $this->subject->callEventFactory());
        $this->assertSame($this->values, iterator_to_array($this->subject));
        $this->assertSame($this->values, iterator_to_array($this->subject));
    }

    public function testConstructorDefaults()
    {
        $this->subject = new IteratorSpy($this->call, $this->iterator);

        $this->assertSame(CallEventFactory::instance(), $this->subject->callEventFactory());
    }
}
