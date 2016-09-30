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

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;

class SpyDataTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->label = 'label';
        $this->callFactory = new TestCallFactory();
        $this->invoker = new Invoker();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->generatorSpyFactory = new GeneratorSpyFactory($this->callEventFactory, FeatureDetector::instance());
        $this->iterableSpyFactory = new IterableSpyFactory($this->callEventFactory);
        $this->subject = new SpyData(
            $this->callback,
            $this->label,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );

        $this->callA = $this->callFactory->create();
        $this->callB = $this->callFactory->create();
        $this->calls = array($this->callA, $this->callB);

        $this->callFactory->reset();
    }

    public function testConstructor()
    {
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->label, $this->subject->label());
        $this->assertTrue($this->subject->useGeneratorSpies());
        $this->assertFalse($this->subject->useIterableSpies());
        $this->assertSame(array(), $this->subject->allCalls());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(null));
        $this->assertNull($this->subject->label());

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

        $this->assertSame(array($this->callA), $this->subject->allCalls());

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
        $this->assertSame(0, count($this->subject));

        $this->subject->addCall($this->callA);

        $this->assertSame(1, $this->subject->callCount());
        $this->assertSame(1, count($this->subject));
    }

    public function testAllEvents()
    {
        $this->assertSame(array(), $this->subject->allEvents());

        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->allEvents());
    }

    public function testAllCalls()
    {
        $this->assertSame(array(), $this->subject->allCalls());

        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->allCalls());
        $this->assertSame(array($this->callA), iterator_to_array($this->subject));
    }

    public function testFirstEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->firstEvent());
    }

    public function testFirstEventFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->firstEvent();
    }

    public function testLastEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callB, $this->subject->lastEvent());
    }

    public function testLastEventFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
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
        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->eventAt();
    }

    public function testFirstCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callB, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
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
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->callAt();
    }

    public function testInvokeMethods()
    {
        $spy = $this->subject;
        $spy->invokeWith(array(array('a')));
        $spy->invoke(array('b', 'c'));
        $spy(array('d'));
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create(array('a'))),
                ($responseEvent = $this->callEventFactory->createReturned('a')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create(array('b', 'c'))),
                ($responseEvent = $this->callEventFactory->createReturned('bc')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create(array('d'))),
                ($responseEvent = $this->callEventFactory->createReturned('d')),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new SpyData(
            null,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->invokeWith(array('a'));
        $spy->invoke('b', 'c');
        $spy('d');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('a')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('b', 'c')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('d')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = array(new Exception(), new Exception(), new Exception());
        $callback = function () use (&$exceptions) {
            list(, $exception) = each($exceptions);
            throw $exception;
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        try {
            $spy->invokeWith(array('a'));
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
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('a')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[0])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('b', 'c')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[1])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('d')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[2])),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithDefaults()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->invokeWith();
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy),
                ($responseEvent = $this->callEventFactory->createReturned('x')),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'x';
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $value = null;
        $arguments = array(&$value);
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
            null,
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
            $this->callFactory->create($this->callEventFactory->createCalled($spy, Arguments::create('a', 'b')));
        $iterableSpyA = $this->iterableSpyFactory->create($expectedCallA, array('A', 'B'));
        $expectedCallA->setResponseEvent($this->callEventFactory->createReturned(array('A', 'B')));
        iterator_to_array($iterableSpyA);
        $expectedCallB =
            $this->callFactory->create($this->callEventFactory->createCalled($spy, Arguments::create('c')));
        $iterableSpyB = $this->iterableSpyFactory->create($expectedCallB, array('C'));
        $expectedCallB->setResponseEvent($this->callEventFactory->createReturned(array('C')));
        iterator_to_array($iterableSpyB);
        $expected = array($expectedCallA, $expectedCallB);

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithIterableSpyDoubleWrap()
    {
        $this->callback = function ($a) {
            return $a;
        };
        $spy = new SpyData(
            $this->callback,
            null,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->setUseIterableSpies(true);
        $iterableSpyA = $spy->invoke(array());
        $iterableSpyB = $spy->invoke($iterableSpyA);

        $this->assertInstanceOf('Eloquent\Phony\Spy\IterableSpy', $iterableSpyA);
        $this->assertInstanceOf('Eloquent\Phony\Spy\IterableSpy', $iterableSpyB);
        $this->assertNotSame($iterableSpyA, $iterableSpyB);
    }

    public function testStopRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->stopRecording()->invokeWith();
        $this->callFactory->reset();

        $this->assertSame(array(), $spy->allCalls());
    }

    public function testStartRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        $spy->stopRecording()->invoke('a');
        $spy->startRecording()->invoke('b');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('b')),
                ($responseEvent = $this->callEventFactory->createReturned('x')),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }
}
