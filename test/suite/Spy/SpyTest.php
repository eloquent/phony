<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Spy\Factory\GeneratorSpyFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;

class SpyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->label = 'label';
        $this->useGeneratorSpies = false;
        $this->useTraversableSpies = false;
        $this->indexNormalizer = new IndexNormalizer();
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->generatorSpyFactory = new GeneratorSpyFactory($this->callEventFactory);
        $this->traversableSpyFactory = new TraversableSpyFactory($this->callEventFactory);
        $this->subject = new Spy(
            $this->callback,
            $this->label,
            $this->useGeneratorSpies,
            $this->useTraversableSpies,
            $this->indexNormalizer,
            $this->callFactory,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
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
        $this->assertSame($this->useGeneratorSpies, $this->subject->useGeneratorSpies());
        $this->assertSame($this->useTraversableSpies, $this->subject->useTraversableSpies());
        $this->assertSame($this->indexNormalizer, $this->subject->indexNormalizer());
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame($this->generatorSpyFactory, $this->subject->generatorSpyFactory());
        $this->assertSame($this->traversableSpyFactory, $this->subject->traversableSpyFactory());
        $this->assertSame(array(), $this->subject->allCalls());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Spy();

        $this->assertTrue($this->subject->isAnonymous());
        $this->assertTrue(is_callable($this->subject->callback()));
        $this->assertNull($this->subject->label());
        $this->assertTrue($this->subject->useGeneratorSpies());
        $this->assertFalse($this->subject->useTraversableSpies());
        $this->assertNull(call_user_func($this->subject->callback()));
        $this->assertSame(IndexNormalizer::instance(), $this->subject->indexNormalizer());
        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
        $this->assertSame(GeneratorSpyFactory::instance(), $this->subject->generatorSpyFactory());
        $this->assertSame(TraversableSpyFactory::instance(), $this->subject->traversableSpyFactory());
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
        $this->subject->setUseGeneratorSpies(true);

        $this->assertTrue($this->subject->useGeneratorSpies());
    }

    public function testSetUseTraversableSpies()
    {
        $this->subject->setUseTraversableSpies(true);

        $this->assertTrue($this->subject->useTraversableSpies());
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

    public function testArguments()
    {
        $arguments = Arguments::adapt(array('a', 1));
        $this->subject->addCall($this->callFactory->create($this->callEventFactory->createCalled(null, $arguments)));

        $this->assertSame($arguments, $this->subject->arguments());
    }

    public function testArgumentsFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->arguments();
    }

    public function testArgument()
    {
        $arguments = Arguments::adapt(array('a', 1));
        $this->subject->addCall($this->callFactory->create($this->callEventFactory->createCalled(null, $arguments)));

        $this->assertSame('a', $this->subject->argument());
        $this->assertSame('a', $this->subject->argument(0));
        $this->assertSame('a', $this->subject->argument(-2));
    }

    public function testArgumentFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->argument();
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
                $this->callEventFactory->createCalled($spy, array(array('a'))),
                $this->callEventFactory->createReturned('a')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array(array('b', 'c'))),
                $this->callEventFactory->createReturned('bc')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array(array('d'))),
                $this->callEventFactory->createReturned('d')
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, '111', false, false, null, $this->callFactory);
        $spy->invokeWith(array('a'));
        $spy->invoke('b', 'c');
        $spy('d');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('a')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('b', 'c')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('d')),
                $this->callEventFactory->createReturned()
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
        $spy = new Spy($callback, '111', false, false, null, $this->callFactory);
        $caughtExceptions = array();
        try {
            $spy->invokeWith(array('a'));
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        try {
            $spy->invoke('b', 'c');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        try {
            $spy('d');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('a')),
                $this->callEventFactory->createThrew($exceptions[0])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('b', 'c')),
                $this->callEventFactory->createThrew($exceptions[1])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('d')),
                $this->callEventFactory->createThrew($exceptions[2])
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithDefaults()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new Spy($callback, '111', false, false, null, $this->callFactory);
        $spy->invokeWith();
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy),
                $this->callEventFactory->createReturned('x')
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'x';
        };
        $spy = new Spy($callback, '111', false, false, null, $this->callFactory);
        $value = null;
        $arguments = array(&$value);
        $spy->invokeWith($arguments);

        $this->assertSame('x', $value);
    }
}
