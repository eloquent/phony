<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class GeneratorSpyFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->featureDetector = new FeatureDetector();
        $this->subject = new GeneratorSpyFactory($this->callEventFactory, $this->featureDetector);

        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned(GeneratorFactory::createEmpty())
        );
        $this->callFactory->reset();
    }

    public function testCreateWithReturnedNullEnd()
    {
        $generator = call_user_func(
            function () {
                yield 'a';
                yield 'b';
                yield 'c';
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        while ($spy->valid()) {
            $spy->send(strtoupper($spy->current()));
        }
        $this->callFactory->reset();
        $generatorEvents = [
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceived('B'),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        ];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createReturned(null);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $spy->_phonySubject);
        $this->assertEquals($generatorEvents, $this->call->iterableEvents());
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
            while ($spy->valid()) {
                $spy->send(strtoupper($spy->current()));
            }
        } catch (RuntimeException $caughtException) {
        }
        $this->callFactory->reset();
        $generatorEvents = [
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceived('B'),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        ];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $spy->_phonySubject);
        $this->assertEquals($generatorEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($exception, $caughtException);
    }

    public function testCreateWithReceivedException()
    {
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
        while ($spy->valid()) {
            if (1 === $spy->key()) {
                $spy->throw($receivedException);
            } else {
                $spy->send(strtoupper($spy->current()));
            }
        }
        $this->callFactory->reset();
        $generatorEvents = [
            $this->callEventFactory->createUsed(),
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceivedException($receivedException),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        ];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createReturned(null);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $spy->_phonySubject);
        $this->assertEquals($generatorEvents, $this->call->iterableEvents());
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
        $generatorEvents = [$this->callEventFactory->createUsed()];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createReturned(null);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $spy->_phonySubject);
        $this->assertEquals($generatorEvents, $this->call->iterableEvents());
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
        }
        $this->callFactory->reset();
        $generatorEvents = [$this->callEventFactory->createUsed()];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $spy->_phonySubject);
        $this->assertEquals($generatorEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($exception, $caughtException);
    }

    public function testGeneratorReturn()
    {
        $generator = eval(
            'return call_user_func(function () { return 123; yield; });'
        );

        $spy = $this->subject->create($this->call, $generator, true);

        while ($spy->valid()) {
            $spy->next();
        }

        $this->assertSame(
            123,
            $spy->getReturn()
        );
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
