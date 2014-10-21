<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;

class CallEventCollectionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->eventA = $this->callEventFactory->createReturned();
        $this->eventB = $this->callFactory->create($this->callEventFactory->createCalled(null, array('a', 'b')));
        $this->eventC = $this->callEventFactory->createCalled(null, array('c', 'd'));
        $this->events = array($this->eventA, $this->eventB, $this->eventC);
        $this->subject = new CallEventCollection($this->events);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->events, $this->subject->events());
        $this->assertSame($this->eventA, $this->subject->firstEvent());
        $this->assertSame($this->eventC, $this->subject->lastEvent());
        $this->assertSame(3, count($this->subject));
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallEventCollection();

        $this->assertFalse($this->subject->hasEvents());
        $this->assertSame(array(), $this->subject->events());
        $this->assertNull($this->subject->firstEvent());
        $this->assertNull($this->subject->lastEvent());
        $this->assertSame(0, count($this->subject));
    }

    public function testArguments()
    {
        $this->assertSame(array('a', 'b'), $this->subject->arguments());

        $this->subject = new CallEventCollection(array($this->eventA, $this->eventC));

        $this->assertSame(array('c', 'd'), $this->subject->arguments());

        $this->subject = new CallEventCollection(array($this->eventA));

        $this->assertNull($this->subject->arguments());

        $this->subject = new CallEventCollection();

        $this->assertNull($this->subject->arguments());
    }

    public function testArgument()
    {
        $this->assertSame('a', $this->subject->argument());
        $this->assertSame('a', $this->subject->argument(0));
        $this->assertSame('b', $this->subject->argument(1));

        $this->subject = new CallEventCollection(array($this->eventA, $this->eventC));

        $this->assertSame('c', $this->subject->argument());
        $this->assertSame('c', $this->subject->argument(0));
        $this->assertSame('d', $this->subject->argument(1));
    }

    public function testArgumentFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->argument(111);
    }

    public function testArgumentFailureNoCalledEvents()
    {
        $this->subject = new CallEventCollection(array($this->eventA));

        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->argument();
    }

    public function testArgumentFailureNoEvents()
    {
        $this->subject = new CallEventCollection();

        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->argument();
    }

    public function testIteration()
    {
        $this->assertSame($this->events, iterator_to_array($this->subject));
    }
}
