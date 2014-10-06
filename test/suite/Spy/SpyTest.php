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
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;

class SpyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->callFactory = new TestCallFactory();
        $this->subject = new Spy($this->callback, $this->callFactory);

        $this->callA = $this->callFactory->create();
        $this->callB = $this->callFactory->create();
        $this->calls = array($this->callA, $this->callB);

        $this->callFactory->reset();
        $this->callEventFactory = $this->callFactory->eventFactory();
    }

    public function testConstructor()
    {
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame(array(), $this->subject->recordedCalls());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Spy();

        $this->assertTrue(is_callable($this->subject->callback()));
        $this->assertNull(call_user_func($this->subject->callback()));
        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
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
                $this->callEventFactory->createCalled($spy->callback(), array(array('a'))),
                $this->callEventFactory->createReturned('a')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array(array('b', 'c'))),
                $this->callEventFactory->createReturned('bc')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array(array('d'))),
                $this->callEventFactory->createReturned('d')
            ),
        );

        $this->assertEquals($expected, $spy->recordedCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, $this->callFactory);
        $spy->invokeWith(array('a'));
        $spy->invoke('b', 'c');
        $spy('d');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('a')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('b', 'c')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('d')),
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
        $spy = new Spy($callback, $this->callFactory);
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
                $this->callEventFactory->createCalled($spy->callback(), array('a')),
                $this->callEventFactory->createThrew($exceptions[0])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('b', 'c')),
                $this->callEventFactory->createThrew($exceptions[1])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('d')),
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
        $spy = new Spy($callback, $this->callFactory);
        $spy->invokeWith();
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($callback),
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
        $spy = new Spy($callback, $this->callFactory);
        $value = null;
        $arguments = array(&$value);
        $spy->invokeWith($arguments);

        $this->assertSame('x', $value);
    }
}
