<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\GeneratorFactory;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[AllowDynamicProperties]
class GeneratorSpyFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;

        $this->subject = $this->container->generatorSpyFactory;
        $this->generatorSpyMap = $this->container->generatorSpyMap;

        $this->call = $this->callFactory->create(
            $this->eventFactory->createCalled(),
            $this->eventFactory->createReturned(GeneratorFactory::createEmpty())
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
            $this->eventFactory->createUsed(),
            $this->eventFactory->createProduced(0, 'a'),
            $this->eventFactory->createReceived('A'),
            $this->eventFactory->createProduced(1, 'b'),
            $this->eventFactory->createReceived('B'),
            $this->eventFactory->createProduced(2, 'c'),
            $this->eventFactory->createReceived('C'),
        ];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createReturned(null);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $this->generatorSpyMap->get($spy));
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
            $this->eventFactory->createUsed(),
            $this->eventFactory->createProduced(0, 'a'),
            $this->eventFactory->createReceived('A'),
            $this->eventFactory->createProduced(1, 'b'),
            $this->eventFactory->createReceived('B'),
            $this->eventFactory->createProduced(2, 'c'),
            $this->eventFactory->createReceived('C'),
        ];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $this->generatorSpyMap->get($spy));
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
            $this->eventFactory->createUsed(),
            $this->eventFactory->createProduced(0, 'a'),
            $this->eventFactory->createReceived('A'),
            $this->eventFactory->createProduced(1, 'b'),
            $this->eventFactory->createReceivedException($receivedException),
            $this->eventFactory->createProduced(2, 'c'),
            $this->eventFactory->createReceived('C'),
        ];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createReturned(null);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $this->generatorSpyMap->get($spy));
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
        $generatorEvents = [$this->eventFactory->createUsed()];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createReturned(null);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $this->generatorSpyMap->get($spy));
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
        $generatorEvents = [$this->eventFactory->createUsed()];
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->eventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf(Generator::class, $spy);
        $this->assertSame($generator, $this->generatorSpyMap->get($spy));
        $this->assertEquals($generatorEvents, $this->call->iterableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
        $this->assertSame($exception, $caughtException);
    }

    public function testGeneratorReturn()
    {
        $generator = call_user_func(function () {
            return 123;
            yield;
        });
        $spy = $this->subject->create($this->call, $generator, true);

        while ($spy->valid()) {
            $spy->next();
        }

        $this->assertSame(123, $spy->getReturn());
    }
}
