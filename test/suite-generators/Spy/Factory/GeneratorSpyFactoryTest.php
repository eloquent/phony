<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class GeneratorSpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->featureDetector = new FeatureDetector();
        $this->subject = new GeneratorSpyFactory($this->callEventFactory, $this->featureDetector);

        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createGenerated()
        );
        $this->callFactory->reset();
    }

    public function testConstructor()
    {
        $this->assertSame($this->callEventFactory, $this->subject->callEventFactory());
        $this->assertSame($this->featureDetector, $this->subject->featureDetector());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new GeneratorSpyFactory();

        $this->assertSame(CallEventFactory::instance(), $this->subject->callEventFactory());
        $this->assertSame(FeatureDetector::instance(), $this->subject->featureDetector());
    }

    public function testIsSupported()
    {
        $generator = call_user_func(
            function () {
                return;
                yield null;
            }
        );

        $this->assertTrue($this->subject->isSupported($generator));
    }

    public function testCreateWithConsumedEnd()
    {
        $generator = call_user_func(
            function () {
                yield 'a';
                yield 'b';
                yield 'c';
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $spy->next();
        }

        while ($spy->valid()) {
            $spy->send(strtoupper($spy->current()));
        }
        $this->callFactory->reset();
        $generatorEvents = array(
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceived('B'),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        );
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }

    public function testCreateWithThrownExceptionEnd()
    {
        $exception = new RuntimeException('Consequences will never be the same.');
        $generator = call_user_func(
            function () use ($exception) {
                yield 'a';
                yield 'b';
                yield 'c';

                throw $exception;
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        $caughtException = null;
        try {
            if ($this->featureDetector->isSupported('runtime.hhvm')) {
                $spy->next();
            }

            while ($spy->valid()) {
                $spy->send(strtoupper($spy->current()));
            }
        } catch (RuntimeException $caughtException) {
        }
        $this->callFactory->reset();
        $generatorEvents = array(
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceived('B'),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        );
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($exception, $caughtException);
    }

    public function testCreateWithReceivedException()
    {
        if (!$this->featureDetector->isSupported('generator.exception')) {
            $this->markTestSkipped('Requires generator exception support.');
        }

        $receivedException = new RuntimeException('You done goofed.');
        $generator = call_user_func(
            function () {
                yield 'a';

                try {
                    yield 'b';
                } catch (RuntimeException $receivedException) {
                }

                yield 'c';
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $spy->next();
        }

        while ($spy->valid()) {
            if (1 === $spy->key()) {
                $spy->throw($receivedException);
            } else {
                $spy->send(strtoupper($spy->current()));
            }
        }
        $this->callFactory->reset();
        $generatorEvents = array(
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceivedException($receivedException),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        );
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
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
        $spy = $this->subject->create($this->call, $generator, true);
        foreach ($spy as $value) {
        }
        $this->callFactory->reset();
        $generatorEvents = array();
        $endEvent = $this->callEventFactory->createConsumed();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
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
        $spy = $this->subject->create($this->call, $generator, true);
        $caughtException = null;
        try {
            foreach ($spy as $value) {
            }
        } catch (RuntimeException $caughtException) {
        };
        $this->callFactory->reset();
        $generatorEvents = array();
        $endEvent = $this->callEventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($exception, $caughtException);
    }

    public function testCreateFailureInvalidTraversable()
    {
        $this->call = $this->callFactory->create();

        $this->setExpectedException('InvalidArgumentException', 'Unsupported traversable of type NULL.');
        $this->subject->create($this->call, null);
    }

    public function testCreateFailureInvalidTraversableObject()
    {
        $this->call = $this->callFactory->create();

        $this->setExpectedException('InvalidArgumentException', "Unsupported traversable of type 'stdClass'.");
        $this->subject->create($this->call, (object) array());
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
