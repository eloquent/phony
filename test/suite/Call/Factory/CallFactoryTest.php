<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Test\TestCallEventFactory;
use Error;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class CallFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->eventFactory = new TestCallEventFactory();
        $this->invoker = new Invoker();
        $this->indexNormalizer = new IndexNormalizer();
        $this->subject = new CallFactory($this->eventFactory, $this->invoker, $this->indexNormalizer);

        $this->featureDetector = new FeatureDetector();
    }

    public function testConstructor()
    {
        $this->assertSame($this->eventFactory, $this->subject->eventFactory());
        $this->assertSame($this->invoker, $this->subject->invoker());
        $this->assertSame($this->indexNormalizer, $this->subject->indexNormalizer());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallFactory();

        $this->assertSame(CallEventFactory::instance(), $this->subject->eventFactory());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
        $this->assertSame(IndexNormalizer::instance(), $this->subject->indexNormalizer());
    }

    public function testRecord()
    {
        $callback = 'implode';
        $arguments = Arguments::create(array('a', 'b'));
        $returnValue = 'ab';
        $spy = new Spy();
        $expected = $this->subject->create(
            $this->eventFactory->createCalled($spy, $arguments),
            $this->eventFactory->createReturned($returnValue)
        );
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(array($expected), $spy->allCalls());
    }

    public function testRecordException()
    {
        $exception = new RuntimeException('You done goofed.');
        $callback = function () use ($exception) {
            throw $exception;
        };
        $arguments = Arguments::create(array('a', 'b'));
        $spy = new Spy();
        $expected = $this->subject->create(
            $this->eventFactory->createCalled($spy, $arguments),
            $this->eventFactory->createThrew($exception)
        );
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(array($expected), $spy->allCalls());
    }

    public function testRecordEngineErrorException()
    {
        if (!$this->featureDetector->isSupported('error.exception.engine')) {
            $this->markTestSkipped('Requires engine error exceptions.');
        }

        $exception = new Error('You done goofed.');
        $callback = function () use ($exception) {
            throw $exception;
        };
        $arguments = Arguments::create(array('a', 'b'));
        $spy = new Spy();
        $expected = $this->subject->create(
            $this->eventFactory->createCalled($spy, $arguments),
            $this->eventFactory->createThrew($exception)
        );
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(array($expected), $spy->allCalls());
    }

    public function testRecordWithoutSpy()
    {
        $callback = 'implode';
        $arguments = Arguments::create(array('a', 'b'));
        $returnValue = 'ab';
        $expected = $this->subject->create(
            $this->eventFactory->createCalled($callback, $arguments),
            $this->eventFactory->createReturned($returnValue)
        );
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments);

        $this->assertEquals($expected, $actual);
    }

    public function testRecordDefaults()
    {
        $actual = $this->subject->record();

        $this->assertInstanceOf('Eloquent\Phony\Call\Call', $actual);

        $this->assertInstanceOf('Eloquent\Phony\Call\Event\CalledEvent', $actual->calledEvent());
        $this->assertEquals(new Arguments(), $actual->calledEvent()->arguments());
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
        $this->assertSame($this->indexNormalizer, $actual->indexNormalizer());
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
