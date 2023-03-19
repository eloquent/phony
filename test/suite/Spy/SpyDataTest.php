<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

#[AllowDynamicProperties]
class SpyDataTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;

        $this->callback = 'implode';
        $this->parameters = (new ReflectionFunction('implode'))->getParameters();
        $this->label = 'label';

        $this->subject = new SpyData(
            $this->callback,
            $this->parameters,
            $this->label,
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );

        $this->generatorSpyFactory = $this->container->generatorSpyFactory;
        $this->iterableSpyFactory = $this->container->iterableSpyFactory;
        $this->invoker = $this->container->invoker;

        $this->callA = $this->callFactory->create();
        $this->callB = $this->callFactory->create();
        $this->calls = [$this->callA, $this->callB];

        $this->callFactory->reset();
    }

    public function testConstructor()
    {
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->parameters, $this->subject->parameters());
        $this->assertSame($this->label, $this->subject->label());
        $this->assertTrue($this->subject->useGeneratorSpies());
        $this->assertFalse($this->subject->useIterableSpies());
        $this->assertSame([], $this->subject->allCalls());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(''));
        $this->assertSame('', $this->subject->label());

        $this->subject->setLabel($this->label);

        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetUseGeneratorSpies()
    {
        $this->assertSame($this->subject, $this->subject->setUseGeneratorSpies(true));
        $this->assertTrue($this->subject->useGeneratorSpies());
    }

    public function testSetUseIterableSpies()
    {
        $this->assertSame($this->subject, $this->subject->setUseIterableSpies(true));
        $this->assertTrue($this->subject->useIterableSpies());
    }

    public function testSetCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->calls, $this->subject->allCalls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame([$this->callA], $this->subject->allCalls());

        $this->subject->addCall($this->callB);

        $this->assertSame($this->calls, $this->subject->allCalls());
    }

    public function testHasEvents()
    {
        $this->assertFalse($this->subject->hasEvents());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->hasEvents());
    }

    public function testHasCalls()
    {
        $this->assertFalse($this->subject->hasCalls());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->hasCalls());
    }

    public function testEventCount()
    {
        $this->assertSame(0, $this->subject->eventCount());

        $this->subject->addCall($this->callA);

        $this->assertSame(1, $this->subject->eventCount());
    }

    public function testCallCount()
    {
        $this->assertSame(0, $this->subject->callCount());
        $this->assertCount(0, $this->subject);

        $this->subject->addCall($this->callA);

        $this->assertSame(1, $this->subject->callCount());
        $this->assertCount(1, $this->subject);
    }

    public function testAllEvents()
    {
        $this->assertSame([], $this->subject->allEvents());

        $this->subject->addCall($this->callA);

        $this->assertSame([$this->callA], $this->subject->allEvents());
    }

    public function testAllCalls()
    {
        $this->assertSame([], $this->subject->allCalls());

        $this->subject->addCall($this->callA);

        $this->assertSame([$this->callA], $this->subject->allCalls());
        $this->assertSame([$this->callA], iterator_to_array($this->subject));
    }

    public function testFirstEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->firstEvent());
    }

    public function testFirstEventFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedEventException::class);
        $this->subject->firstEvent();
    }

    public function testLastEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callB, $this->subject->lastEvent());
    }

    public function testLastEventFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedEventException::class);
        $this->subject->lastEvent();
    }

    public function testEventAt()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame($this->callA, $this->subject->eventAt());
        $this->assertSame($this->callA, $this->subject->eventAt(0));
        $this->assertSame($this->callA, $this->subject->eventAt(-1));
    }

    public function testEventAtFailure()
    {
        $this->expectException(UndefinedEventException::class);
        $this->subject->eventAt();
    }

    public function testFirstCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedCallException::class);
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callB, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedCallException::class);
        $this->subject->lastCall();
    }

    public function testCallAt()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame($this->callA, $this->subject->callAt());
        $this->assertSame($this->callA, $this->subject->callAt(0));
        $this->assertSame($this->callA, $this->subject->callAt(-1));
    }

    public function testCallAtFailure()
    {
        $this->expectException(UndefinedCallException::class);
        $this->subject->callAt();
    }

    public function testInvokeMethods()
    {
        $spy = $this->subject;
        $spy->invokeWith([['a']]);
        $spy->invoke(['b', 'c']);
        $spy(['d']);
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, $this->parameters, Arguments::create(['a'])),
                ($responseEvent = $this->eventFactory->createReturned('a')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, $this->parameters, Arguments::create(['b', 'c'])),
                ($responseEvent = $this->eventFactory->createReturned('bc')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, $this->parameters, Arguments::create(['d'])),
                ($responseEvent = $this->eventFactory->createReturned('d')),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeMethodsWithNamedArguments()
    {
        $spy = $this->subject;
        $spy->invokeWith(['array' => ['1'], 'separator' => ',']);
        $spy->invoke(array: ['2', '3'], separator: ',');
        $spy(array: ['4'], separator: ',');
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->eventFactory
                    ->createCalled($spy, $this->parameters, Arguments::create(array: ['1'], separator: ',')),
                ($responseEvent = $this->eventFactory->createReturned('1')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory
                    ->createCalled($spy, $this->parameters, Arguments::create(array: ['2', '3'], separator: ',')),
                ($responseEvent = $this->eventFactory->createReturned('2,3')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory
                    ->createCalled($spy, $this->parameters, Arguments::create(array: ['4'], separator: ',')),
                ($responseEvent = $this->eventFactory->createReturned('4')),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new SpyData(
            null,
            [],
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->invokeWith(['a']);
        $spy->invoke('b', 'c');
        $spy('d');
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, [], Arguments::create('a')),
                ($responseEvent = $this->eventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, [], Arguments::create('b', 'c')),
                ($responseEvent = $this->eventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, [], Arguments::create('d')),
                ($responseEvent = $this->eventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = [new Exception(), new Exception(), new Exception()];
        $index = 0;
        $callback = function () use (&$exceptions, &$index) {
            $exception = $exceptions[$index++];
            throw $exception;
        };
        $spy = new SpyData(
            $callback,
            [],
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        try {
            $spy->invokeWith(['a']);
        } catch (Exception $caughtException) {
        }
        try {
            $spy->invoke('b', 'c');
        } catch (Exception $caughtException) {
        }
        try {
            $spy('d');
        } catch (Exception $caughtException) {
        }
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, [], Arguments::create('a')),
                ($responseEvent = $this->eventFactory->createThrew($exceptions[0])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, [], Arguments::create('b', 'c')),
                ($responseEvent = $this->eventFactory->createThrew($exceptions[1])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, [], Arguments::create('d')),
                ($responseEvent = $this->eventFactory->createThrew($exceptions[2])),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithDefaults()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            [],
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->invokeWith();
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy),
                ($responseEvent = $this->eventFactory->createReturned('x')),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'x';
        };
        $spy = new SpyData(
            $callback,
            (new ReflectionFunction($callback))->getParameters(),
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $value = null;
        $arguments = [&$value];
        $spy->invokeWith($arguments);

        $this->assertSame('x', $value);
    }

    public function testInvokeWithWithIterableSpy()
    {
        $this->callback = function () {
            return array_map('strtoupper', func_get_args());
        };
        $spy = new SpyData(
            $this->callback,
            [],
            '',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->setUseIterableSpies(true);
        foreach ($spy->invoke('a', 'b') as $value) {
        }
        foreach ($spy->invoke('c') as $value) {
        }
        $this->callFactory->reset();
        $expectedCallA =
            $this->callFactory->create($this->eventFactory->createCalled($spy, [], Arguments::create('a', 'b')));
        $iterableSpyA = $this->iterableSpyFactory->create($expectedCallA, ['A', 'B']);
        $expectedCallA->setResponseEvent($this->eventFactory->createReturned(['A', 'B']));
        iterator_to_array($iterableSpyA);
        $expectedCallB =
            $this->callFactory->create($this->eventFactory->createCalled($spy, [], Arguments::create('c')));
        $iterableSpyB = $this->iterableSpyFactory->create($expectedCallB, ['C']);
        $expectedCallB->setResponseEvent($this->eventFactory->createReturned(['C']));
        iterator_to_array($iterableSpyB);
        $expected = [$expectedCallA, $expectedCallB];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithIterableSpyDoubleWrap()
    {
        $this->callback = function ($a) {
            return $a;
        };
        $spy = new SpyData(
            $this->callback,
            (new ReflectionFunction($this->callback))->getParameters(),
            '',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->setUseIterableSpies(true);
        $iterableSpyA = $spy->invoke([]);
        $iterableSpyB = $spy->invoke($iterableSpyA);

        $this->assertInstanceOf(IterableSpy::class, $iterableSpyA);
        $this->assertInstanceOf(IterableSpy::class, $iterableSpyB);
        $this->assertNotSame($iterableSpyA, $iterableSpyB);
    }

    public function testStopRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            [],
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->stopRecording()->invokeWith();
        $this->callFactory->reset();

        $this->assertSame([], $spy->allCalls());
    }

    public function testStartRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            [],
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->stopRecording()->invoke('a');
        $spy->startRecording()->invoke('b');
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->eventFactory->createCalled($spy, [], Arguments::create('b')),
                ($responseEvent = $this->eventFactory->createReturned('x')),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithWithGeneratorSpy()
    {
        $this->callback = function () {
            foreach (func_get_args() as $argument) {
                yield strtoupper($argument);
            }
        };
        $spy = new SpyData(
            $this->callback,
            [],
            '',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        foreach ($spy->invoke('a', 'b') as $value) {
        }
        foreach ($spy->invoke('c') as $value) {
        }
        $this->callFactory->reset();
        $generatorA = call_user_func($this->callback, 'a', 'b');
        $generatorB = call_user_func($this->callback, 'c');
        $expectedCallA =
            $this->callFactory->create($this->eventFactory->createCalled($spy, [], Arguments::create('a', 'b')));
        $generatorSpyA = $this->generatorSpyFactory->create($expectedCallA, $generatorA);
        $expectedCallA->setResponseEvent($this->eventFactory->createReturned($generatorA));
        iterator_to_array($generatorSpyA);
        $expectedCallB =
            $this->callFactory->create($this->eventFactory->createCalled($spy, [], Arguments::create('c')));
        $generatorSpyB = $this->generatorSpyFactory->create($expectedCallB, $generatorB);
        $expectedCallB->setResponseEvent($this->eventFactory->createReturned($generatorB));
        iterator_to_array($generatorSpyB);
        $expected = [$expectedCallA, $expectedCallB];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithGeneratorSpyDoubleWrap()
    {
        $this->callback = function ($a) {
            return $a;
        };
        $spy = new SpyData(
            $this->callback,
            (new ReflectionFunction($this->callback))->getParameters(),
            '',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $function = function () {
            return;
            yield;
        };
        $generator = $function();
        $generatorSpyA = $spy->invoke($generator);
        $generatorSpyB = $spy->invoke($generatorSpyA);

        $this->assertInstanceOf(Generator::class, $generatorSpyA);
        $this->assertInstanceOf(Generator::class, $generatorSpyB);
        $this->assertNotSame($generatorSpyA, $generatorSpyB);
    }
}
