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

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;

class SpyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->spySubject = function () {
            return '= ' .implode(', ', func_get_args());
        };
        $this->reflector = new ReflectionFunction($this->spySubject);
        $this->callFactory = new TestCallFactory();
        $this->subject =
            new Spy($this->spySubject, $this->reflector, $this->callFactory);

        $this->callA = new Call(
            array(
                new CalledEvent(new ReflectionMethod(__METHOD__), $this, array('argumentA', 'argumentB'), 0, .1),
                new ReturnedEvent('returnValue', 1, .2),
            )
        );
        $this->callB = new Call(
            array(
                new CalledEvent(new ReflectionMethod(__METHOD__), null, array(), 2, .3),
                new ThrewEvent(new RuntimeException('message'), 3, .4),
            )
        );
        $this->calls = array($this->callA, $this->callB);
    }

    public function testConstructor()
    {
        $this->assertSame($this->spySubject, $this->subject->subject());
        $this->assertSame($this->reflector, $this->subject->reflector());
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame(array(), $this->subject->calls());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Spy();

        $this->assertInstanceOf('Closure', $this->subject->subject());
        $this->assertTrue($this->subject->reflector()->isClosure());
        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
    }

    public function testConstructorFailureUnsupportedSubject()
    {
        $this->setExpectedException('InvalidArgumentException', "Unsupported callback.");
        new Spy(111);
    }

    public function testReflectorForClosure()
    {
        $this->subject = new Spy(function () {});

        $this->assertInstanceOf('Closure', $this->subject->subject());
        $this->assertTrue($this->subject->reflector()->isClosure());
    }

    public function testReflectorForMethodString()
    {
        $this->subject = new Spy(__METHOD__);

        $this->assertEquals(new ReflectionMethod($this, __FUNCTION__), $this->subject->reflector());
    }

    public function testReflectorForMethodArray()
    {
        $this->subject = new Spy(array($this, __FUNCTION__));

        $this->assertEquals(new ReflectionMethod($this, __FUNCTION__), $this->subject->reflector());
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
        $spy->invokeWith(array('argumentA'));
        $spy->invoke('argumentB', 'argumentC');
        $spy('argumentD');
        $reflector = $spy->reflector();
        $thisValue = $this->thisValue($this->spySubject);
        $expected = array(
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentA'), 0, 0.123),
                    new ReturnedEvent('= argumentA', 1, 1.123),
                )
            ),
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentB', 'argumentC'), 2, 2.123),
                    new ReturnedEvent('= argumentB, argumentC', 3, 3.123),
                )
            ),
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentD'), 4, 4.123),
                    new ReturnedEvent('= argumentD', 5, 5.123),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, null, $this->callFactory);
        $spy->invokeWith(array('argumentA'));
        $spy->invoke('argumentB', 'argumentC');
        $spy('argumentD');
        $reflector = $spy->reflector();
        $thisValue = $this->thisValue($spy->subject());
        $expected = array(
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentA'), 0, 0.123),
                    new ReturnedEvent(null, 1, 1.123),
                )
            ),
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentB', 'argumentC'), 2, 2.123),
                    new ReturnedEvent(null, 3, 3.123),
                )
            ),
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentD'), 4, 4.123),
                    new ReturnedEvent(null, 5, 5.123),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = array(new Exception(), new Exception(), new Exception());
        $subject = function () use (&$exceptions) {
            list(, $exception) = each($exceptions);
            throw $exception;
        };
        $spy = new Spy($subject, null, $this->callFactory);
        $caughtExceptions = array();
        try {
            $spy->invokeWith(array('argumentA'));
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        try {
            $spy->invoke('argumentB', 'argumentC');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        try {
            $spy('argumentD');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        $reflector = $spy->reflector();
        $thisValue = $this->thisValue($this->spySubject);
        $expected = array(
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentA'), 0, 0.123),
                    new ThrewEvent($exceptions[0], 1, 1.123),
                )
            ),
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentB', 'argumentC'), 2, 2.123),
                    new ThrewEvent($exceptions[1], 3, 3.123),
                )
            ),
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array('argumentD'), 4, 4.123),
                    new ThrewEvent($exceptions[2], 5, 5.123),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithDefaults()
    {
        $spy = $this->subject;
        $spy->invokeWith();
        $reflector = $spy->reflector();
        $thisValue = $this->thisValue($this->spySubject);
        $expected = array(
            new Call(
                array(
                    new CalledEvent($reflector, $thisValue, array(), 0, 0.123),
                    new ReturnedEvent('= ', 1, 1.123),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $subject = function (&$argument) {
            $argument = 'value';
        };
        $spy = new Spy($subject, null, $this->callFactory);
        $value = null;
        $arguments = array(&$value);
        $spy->invokeWith($arguments);

        $this->assertSame('value', $value);
    }

    protected function thisValue($closure)
    {
        $reflectorReflector = new ReflectionClass('ReflectionFunction');
        if (!$reflectorReflector->hasMethod('getClosureThis')) {
            return null;
        }

        $reflector = new ReflectionFunction($closure);

        return $reflector->getClosureThis();
    }
}
