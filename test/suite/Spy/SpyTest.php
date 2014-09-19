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
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Clock\TestClock;
use Eloquent\Phony\Sequence\Sequencer;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class SpyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->spySubject = function () {
            return '= ' .implode(', ', func_get_args());
        };
        $this->sequencer = new Sequencer;
        $this->clock = new TestClock;
        $this->subject = new Spy($this->spySubject, $this->sequencer, $this->clock);

        $this->callA = new Call(array(), null, 0, 1.11, 2.22);
        $this->callB = new Call(array(), null, 1, 3.33, 4.44);
        $this->calls = array($this->callA, $this->callB);
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

    public function testConstructor()
    {
        $this->assertTrue($this->subject->hasSubject());
        $this->assertSame($this->spySubject, $this->subject->subject());
        $this->assertSame($this->sequencer, $this->subject->sequencer());
        $this->assertSame($this->clock, $this->subject->clock());
        $this->assertSame(array(), $this->subject->calls());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Spy;

        $this->assertFalse($this->subject->hasSubject());
        $this->assertSame(Sequencer::instance(), $this->subject->sequencer());
        $this->assertSame(SystemClock::instance(), $this->subject->clock());
    }

    public function testSubjectFailureUndefined()
    {
        $this->subject = new Spy;

        $this->setExpectedException('Eloquent\Phony\Spy\Exception\UndefinedSubjectException');
        $this->subject->subject();
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

    public function testInvoke()
    {
        $spy = $this->subject;
        $spy('argumentA');
        $spy('argumentB', 'argumentC');
        $thisValue = $this->thisValue($this->spySubject);
        $expected = array(
            new Call(array('argumentA'), '= argumentA', 0, 0.123, 1.123, null, $thisValue),
            new Call(array('argumentB', 'argumentC'), '= argumentB, argumentC', 1, 2.123, 3.123, null, $thisValue),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithoutSubject()
    {
        $spy = new Spy(null, $this->sequencer, $this->clock);
        $spy('argumentA');
        $spy('argumentB', 'argumentC');
        $thisValue = null;
        $expected = array(
            new Call(array('argumentA'), null, 0, 0.123, 0.123, null, $thisValue),
            new Call(array('argumentB', 'argumentC'), null, 1, 1.123, 1.123, null, $thisValue),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = array(new Exception, new Exception);
        $subject = function () use (&$exceptions) {
            list(, $exception) = each($exceptions);
            throw $exception;
        };
        $spy = new Spy($subject, $this->sequencer, $this->clock);
        $caughtExceptions = array();
        try {
            $spy('argumentA');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        try {
            $spy('argumentB', 'argumentC');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        $thisValue = $this->thisValue($this->spySubject);
        $expected = array(
            new Call(array('argumentA'), null, 0, 0.123, 1.123, $exceptions[0], $thisValue),
            new Call(array('argumentB', 'argumentC'), null, 1, 2.123, 3.123, $exceptions[1], $thisValue),
        );

        $this->assertEquals($expected, $spy->calls());
    }
}
