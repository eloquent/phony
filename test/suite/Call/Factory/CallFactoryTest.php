<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Test\TestCallEventFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class CallFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->eventFactory = new TestCallEventFactory();
        $this->invoker = new Invoker();
        $this->subject = new CallFactory($this->eventFactory, $this->invoker);

        $this->exception = new RuntimeException('You done goofed.');
    }

    public function testConstructor()
    {
        $this->assertSame($this->eventFactory, $this->subject->eventFactory());
        $this->assertSame($this->invoker, $this->subject->invoker());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallFactory();

        $this->assertSame(CallEventFactory::instance(), $this->subject->eventFactory());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
    }

    public function testRecord()
    {
        $callback = 'implode';
        $arguments = array(array('a', 'b'));
        $returnValue = 'ab';
        $expected = $this->subject->create(
            $this->eventFactory->createCalled($callback, $arguments),
            $this->eventFactory->createReturned($returnValue)
        );
        $this->eventFactory->reset();
        $spy = new Spy();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(array($expected), $spy->recordedCalls());
    }

    public function testRecordDefaults()
    {
        $actual = $this->subject->record();

        $this->assertInstanceOf('Eloquent\Phony\Call\Call', $actual);

        $this->assertInstanceOf('Eloquent\Phony\Call\Event\CalledEvent', $actual->calledEvent());
        $this->assertSame(array(), $actual->calledEvent()->arguments());
        $this->assertInstanceOf('Eloquent\Phony\Call\Event\ReturnedEvent', $actual->responseEvent());
        $this->assertNull($actual->responseEvent()->value());
    }

    public function testCreate()
    {
        $calledEvent = $this->eventFactory->createCalled();
        $returnedEvent = $this->eventFactory->createReturned();
        $expected = new Call($calledEvent, $returnedEvent);
        $actual = $this->subject->create($calledEvent, $returnedEvent);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateDefaults()
    {
        $expected = new Call($this->eventFactory->createCalled());
        $this->eventFactory->reset();
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
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
