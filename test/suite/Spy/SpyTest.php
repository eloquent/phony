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

        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
    }

    public function testConstructor()
    {
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame(array(), $this->subject->calls());
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

        $this->assertSame($this->calls, $this->subject->calls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->calls());

        $this->subject->addCall($this->callB);

        $this->assertSame($this->calls, $this->subject->calls());
    }

    public function testInvokeMethods()
    {
        $spy = $this->subject;
        $spy->invokeWith(array(array('a')));
        $spy->invoke(array('b', 'c'));
        $spy(array('d'));
        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
        $expected = array(
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array(array('a'))),
                    $this->callFactory->createReturnedEvent('a'),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array(array('b', 'c'))),
                    $this->callFactory->createReturnedEvent('bc'),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array(array('d'))),
                    $this->callFactory->createReturnedEvent('d'),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, $this->callFactory);
        $spy->invokeWith(array('a'));
        $spy->invoke('b', 'c');
        $spy('d');
        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
        $expected = array(
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array('a')),
                    $this->callFactory->createReturnedEvent(),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array('b', 'c')),
                    $this->callFactory->createReturnedEvent(),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array('d')),
                    $this->callFactory->createReturnedEvent(),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
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
        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
        $expected = array(
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array('a')),
                    $this->callFactory->createThrewEvent($exceptions[0]),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array('b', 'c')),
                    $this->callFactory->createThrewEvent($exceptions[1]),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->callback(), array('d')),
                    $this->callFactory->createThrewEvent($exceptions[2]),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithDefaults()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new Spy($callback, $this->callFactory);
        $spy->invokeWith();
        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
        $expected = array(
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($callback),
                    $this->callFactory->createReturnedEvent('x'),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
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
