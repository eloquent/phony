<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class GeneratorSpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported under HHVM.');
        }

        $this->callFactory = new TestCallFactory();
        $this->subject = new GeneratorSpyFactory($this->callFactory);

        $this->call = $this->callFactory->create();
    }

    public function testConstructor()
    {
        $this->assertSame($this->callFactory, $this->subject->callFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new GeneratorSpyFactory();

        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
    }

    public function testCreateWithReturnedEnd()
    {
        $sentException = new RuntimeException('You done goofed.');
        $generator = call_user_func(
            function () {
                yield 'a';

                try {
                    yield 'b';
                } catch (RuntimeException $sentException) {}

                yield 'c';
            }
        );
        $spy = $this->subject->create($this->call, $generator);
        try {
            while ($spy->valid()) {
                if (1 === $spy->key()) {
                    $spy->throw($sentException);
                } else {
                    $spy->send(strtoupper($spy->current()));
                }
            }
        } catch (RuntimeException $caughtException) {}
        $this->callFactory->sequencer()->set(0);
        $this->callFactory->clock()->setTime(1.0);
        $generatorEvents = array(
            $this->callFactory->createYieldedEvent('a', 0),
            $this->callFactory->createSentEvent('A'),
            $this->callFactory->createYieldedEvent('b', 1),
            $this->callFactory->createSentExceptionEvent($sentException),
            $this->callFactory->createYieldedEvent('c', 2),
            $this->callFactory->createSentEvent('C'),
        );
        $endEvent = $this->callFactory->createReturnedEvent();

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->generatorEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }

    public function testCreateWithThrownExceptionEnd()
    {
        $sentException = new RuntimeException('You done goofed.');
        $exception = new RuntimeException('Consequences will never be the same.');
        $generator = call_user_func(
            function () use ($exception) {
                yield 'a';

                try {
                    yield 'b';
                } catch (RuntimeException $sentException) {}

                yield 'c';

                throw $exception;
            }
        );
        $spy = $this->subject->create($this->call, $generator);
        try {
            while ($spy->valid()) {
                if (1 === $spy->key()) {
                    $spy->throw($sentException);
                } else {
                    $spy->send(strtoupper($spy->current()));
                }
            }
        } catch (RuntimeException $caughtException) {}
        $this->callFactory->sequencer()->set(0);
        $this->callFactory->clock()->setTime(1.0);
        $generatorEvents = array(
            $this->callFactory->createYieldedEvent('a', 0),
            $this->callFactory->createSentEvent('A'),
            $this->callFactory->createYieldedEvent('b', 1),
            $this->callFactory->createSentExceptionEvent($sentException),
            $this->callFactory->createYieldedEvent('c', 2),
            $this->callFactory->createSentEvent('C'),
        );
        $endEvent = $this->callFactory->createThrewEvent($exception);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->generatorEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }

    public function testCreateWithEmptyGenerator()
    {
        $generator = call_user_func(
            function () {
                return;
                yield null;
            }
        );
        $spy = $this->subject->create($this->call, $generator);
        foreach ($spy as $value) {}
        $this->callFactory->sequencer()->set(0);
        $this->callFactory->clock()->setTime(1.0);
        $generatorEvents = array();
        $endEvent = $this->callFactory->createReturnedEvent();

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->generatorEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }

    public function testCreateWithImmediateThrowGenerator()
    {
        $exception = new RuntimeException('You done goofed.');
        $generator = call_user_func(
            function () use ($exception) {
                throw $exception;
                yield null;
            }
        );
        $spy = $this->subject->create($this->call, $generator);
        foreach ($spy as $value) {}
        $this->callFactory->sequencer()->set(0);
        $this->callFactory->clock()->setTime(1.0);
        $generatorEvents = array();
        $endEvent = $this->callFactory->createThrewEvent($exception);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->generatorEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
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
