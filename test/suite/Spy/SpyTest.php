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
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Clock\TestClock;
use Eloquent\Phony\Sequencer\Sequencer;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class SpyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->spySubject = function () {
            return '= ' .implode(', ', func_get_args());
        };
        $this->reflector = new ReflectionFunction($this->spySubject);
        $this->sequencer = new Sequencer();
        $this->clock = new TestClock();
        $this->callFactory = new CallFactory();
        $this->subject =
            new Spy($this->spySubject, $this->reflector, $this->sequencer, $this->clock, $this->callFactory);

        $this->callA = new Call($this->reflector, array(), null, 0, 1.11, 2.22);
        $this->callB = new Call($this->reflector, array(), null, 1, 3.33, 4.44);
        $this->calls = array($this->callA, $this->callB);
    }

    public function testConstructor()
    {
        $this->assertSame($this->spySubject, $this->subject->subject());
        $this->assertSame($this->reflector, $this->subject->reflector());
        $this->assertSame($this->sequencer, $this->subject->sequencer());
        $this->assertSame($this->clock, $this->subject->clock());
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame(array(), $this->subject->calls());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Spy();

        $this->assertInstanceOf('Closure', $this->subject->subject());
        $this->assertTrue($this->subject->reflector()->isClosure());
        $this->assertSame(Sequencer::instance(), $this->subject->sequencer());
        $this->assertSame(SystemClock::instance(), $this->subject->clock());
        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
    }

    public function testConstructorFailureUnsupportedSubject()
    {
        $this->setExpectedException('InvalidArgumentException', "Unsupported spy subject.");
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
            new Call($reflector, array('argumentA'), '= argumentA', 0, 0.123, 1.123, null, $thisValue),
            new Call(
                $reflector,
                array('argumentB', 'argumentC'),
                '= argumentB, argumentC',
                1,
                2.123,
                3.123,
                null,
                $thisValue
            ),
            new Call($reflector, array('argumentD'), '= argumentD', 2, 4.123, 5.123, null, $thisValue),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, null, $this->sequencer, $this->clock);
        $spy->invokeWith(array('argumentA'));
        $spy->invoke('argumentB', 'argumentC');
        $spy('argumentD');
        $reflector = $spy->reflector();
        $thisValue = $this->thisValue($spy->subject());
        $expected = array(
            new Call($reflector, array('argumentA'), null, 0, 0.123, 1.123, null, $thisValue),
            new Call(
                $reflector,
                array('argumentB', 'argumentC'),
                null,
                1,
                2.123,
                3.123,
                null,
                $thisValue
            ),
            new Call($reflector, array('argumentD'), null, 2, 4.123, 5.123, null, $thisValue),
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
        $spy = new Spy($subject, null, $this->sequencer, $this->clock);
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
            new Call($reflector, array('argumentA'), null, 0, 0.123, 1.123, $exceptions[0], $thisValue),
            new Call(
                $reflector,
                array('argumentB', 'argumentC'),
                null,
                1,
                2.123,
                3.123,
                $exceptions[1],
                $thisValue
            ),
            new Call($reflector, array('argumentD'), null, 2, 4.123, 5.123, $exceptions[2], $thisValue),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $subject = function (&$argument) {
            $argument = 'value';
        };
        $spy = new Spy($subject, null, $this->sequencer, $this->clock);
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
