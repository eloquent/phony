<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;

class SpyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->useTraversableSpies = false;
        $this->useGeneratorSpies = false;
        $this->label = 'label';
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->traversableSpyFactory = new TraversableSpyFactory($this->callEventFactory);
        $this->subject = new Spy(
            $this->callback,
            $this->useTraversableSpies,
            $this->useGeneratorSpies,
            $this->label,
            $this->callFactory,
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
        $this->assertSame($this->useTraversableSpies, $this->subject->useTraversableSpies());
        $this->assertSame($this->useGeneratorSpies, $this->subject->useGeneratorSpies());
        $this->assertSame($this->label, $this->subject->label());
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame($this->traversableSpyFactory, $this->subject->traversableSpyFactory());
        $this->assertSame(array(), $this->subject->recordedCalls());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Spy();

        $this->assertTrue($this->subject->isAnonymous());
        $this->assertTrue(is_callable($this->subject->callback()));
        $this->assertFalse($this->subject->useTraversableSpies());
        $this->assertSame(!defined('HHVM_VERSION'), $this->subject->useGeneratorSpies());
        $this->assertNull($this->subject->label());
        $this->assertNull(call_user_func($this->subject->callback()));
        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
        $this->assertSame(TraversableSpyFactory::instance(), $this->subject->traversableSpyFactory());
    }

    public function testSetLabel()
    {
        $this->subject->setLabel(null);

        $this->assertNull($this->subject->label());

        $this->subject->setLabel($this->label);

        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetUseTraversableSpies()
    {
        $this->subject->setUseTraversableSpies(true);

        $this->assertTrue($this->subject->useTraversableSpies());
    }

    public function testSetUseGeneratorSpies()
    {
        $this->subject->setUseGeneratorSpies(true);

        $this->assertTrue($this->subject->useGeneratorSpies());
    }

    public function testSetCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->calls, $this->subject->recordedCalls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->recordedCalls());

        $this->subject->addCall($this->callB);

        $this->assertSame($this->calls, $this->subject->recordedCalls());
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

        $this->assertEquals($expected, $spy->recordedCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, false, false, 111, $this->callFactory);
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

        $this->assertEquals($expected, $spy->recordedCalls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = array(new Exception(), new Exception(), new Exception());
        $callback = function () use (&$exceptions) {
            list(, $exception) = each($exceptions);
            throw $exception;
        };
        $spy = new Spy($callback, false, false, 111, $this->callFactory);
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

        $this->assertEquals($expected, $spy->recordedCalls());
    }

    public function testInvokeWithDefaults()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new Spy($callback, false, false, 111, $this->callFactory);
        $spy->invokeWith();
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy),
                $this->callEventFactory->createReturned('x')
            ),
        );

        $this->assertEquals($expected, $spy->recordedCalls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'x';
        };
        $spy = new Spy($callback, false, false, 111, $this->callFactory);
        $value = null;
        $arguments = array(&$value);
        $spy->invokeWith($arguments);

        $this->assertSame('x', $value);
    }
}
