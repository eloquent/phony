<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Exception;
use PHPUnit_Framework_TestCase;

class StubTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->thisValue = (object) array();
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->subject = new Stub($this->thisValue, $this->matcherFactory, $this->matcherVerifier);

        $this->wildcard = array(WildcardMatcher::instance());
        $this->callbackA = function () { return 'valueA'; };
        $this->callbackB = function () { return 'valueB'; };
        $this->callbackC = function () { return 'valueC'; };
        $this->callbackD = function () { return 'valueD'; };
        $this->callbackE = function () { return 'valueE'; };
        $this->callbackF = function () { return 'valueF'; };
    }

    public function testConstructor()
    {
        $this->assertSame($this->thisValue, $this->subject->thisValue());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Stub();

        $this->assertSame($this->subject, $this->subject->thisValue());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
    }

    public function testSetThisValue()
    {
        $this->subject->setThisValue(null);

        $this->assertSame($this->subject, $this->subject->thisValue());

        $this->thisValue = (object) array();
        $this->subject->setThisValue($this->thisValue);

        $this->assertSame($this->thisValue, $this->subject->thisValue());
    }

    public function testWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->with('argumentA', new EqualToMatcher('argumentB'))
                ->returns('value')
        );
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testWithExactly()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->withExactly('argumentA', new EqualToMatcher('argumentB'))
                ->returns('value')
        );
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertNull(call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testDoes()
    {
        $this->assertSame($this->subject, $this->subject->does($this->callbackA, $this->callbackB));
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
    }

    public function testCalls()
    {
        $callsA = array();
        $callbackA = function ($argument) use (&$callsA) {
            $callsA[] = $argument;
        };
        $callCountB = 0;
        $callbackB = function () use (&$callCountB) {
            $callCountB++;
        };

        $this->assertSame(
            $this->subject,
            $this->subject
                ->calls($callbackA, 'first')->returns()
                ->calls($callbackA, 'second')->calls($callbackB)->returns()
        );
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(array('first'), $callsA);
        $this->assertSame(0, $callCountB);
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(array('first', 'second'), $callsA);
        $this->assertSame(1, $callCountB);
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(array('first', 'second', 'second'), $callsA);
        $this->assertSame(2, $callCountB);
    }

    public function testCallsWith()
    {
        $callsA = array();
        $callbackA = function () use (&$callsA) {
            $callsA[] = func_get_args();
        };
        $callCountB = 0;
        $callbackB = function () use (&$callCountB) {
            $callCountB++;
        };

        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsWith($callbackA, array('first'))->returns()
                ->callsWith($callbackA, array('second'), true)->callsWith($callbackB)->returns()
        );
        $this->assertNull(call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertSame(array(array('first')), $callsA);
        $this->assertSame(0, $callCountB);
        $this->assertNull(call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertSame(array(array('first'), array('second', 'argumentA', 'argumentB')), $callsA);
        $this->assertSame(1, $callCountB);
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(array(array('first'), array('second', 'argumentA', 'argumentB'), array('second')), $callsA);
        $this->assertSame(2, $callCountB);
    }

    public function testCallsWithWithReferenceParameters()
    {
        $callback = function (&$argumentA, &$argumentB) {
            $argumentA = 'valueA';
            $argumentB = 'valueB';
        };
        $value = null;
        $this->subject->callsWith($callback, array(&$value), true);
        $argument = null;
        $this->subject->invokeWith(array(&$argument));

        $this->assertSame('valueA', $value);
        $this->assertSame('valueB', $argument);
    }

    public function testCallsArgument()
    {
        $callsA = array();
        $callbackA = function () use (&$callsA) {
            $callsA[] = func_get_args();
        };
        $callsB = array();
        $callbackB = function () use (&$callsB) {
            $callsB[] = func_get_args();
        };

        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgument(null, 'first')->returns()
                ->callsArgument()->callsArgument(1, 'second')->returns()
        );
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'argument'));
        $this->assertSame(array(array('first')), $callsA);
        $this->assertSame(array(), $callsB);
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'argument'));
        $this->assertSame(array(array('first'), array()), $callsA);
        $this->assertSame(array(array('second')), $callsB);
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'argument'));
        $this->assertSame(array(array('first'), array(), array()), $callsA);
        $this->assertSame(array(array('second'), array('second')), $callsB);
    }

    public function testCallsArgumentWith()
    {
        $callsA = array();
        $callbackA = function () use (&$callsA) {
            $callsA[] = func_get_args();
        };
        $callsB = array();
        $callbackB = function () use (&$callsB) {
            $callsB[] = func_get_args();
        };

        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgumentWith(0, array('first'), true)->returns()
                ->callsArgumentWith()->callsArgumentWith(-2, array('second'))->returns()
        );
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'argument'));
        $this->assertSame(array(array('first', $callbackA, $callbackB, 'argument')), $callsA);
        $this->assertSame(array(), $callsB);
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'argument'));
        $this->assertSame(array(array('first', $callbackA, $callbackB, 'argument'), array()), $callsA);
        $this->assertSame(array(array('second')), $callsB);
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'argument'));
        $this->assertSame(array(array('first', $callbackA, $callbackB, 'argument'), array(), array()), $callsA);
        $this->assertSame(array(array('second'), array('second')), $callsB);
    }

    public function testCallsArgumentWithWithUndefinedArguments()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgumentWith()->returns()
                ->callsArgumentWith(1)->returns()
        );
        $this->assertNull(call_user_func($this->subject));
        $this->assertNull(call_user_func($this->subject, 'argument'));
    }

    public function testCallsArgumentWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'value';
        };
        $value = null;
        $this->subject->callsArgumentWith(null, array(&$value), true);
        $this->subject->invoke($callback);

        $this->assertSame('value', $value);
    }

    public function testReturns()
    {
        $this->assertSame($this->subject, $this->subject->returns('valueA', 'valueB'));
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame($this->subject, $this->subject->with()->returns());
        $this->assertNull(call_user_func($this->subject));
    }

    public function testReturnsArgument()
    {
        $this->assertSame($this->subject, $this->subject->returnsArgument());
        $this->assertSame('argumentA', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('argumentB', call_user_func($this->subject, 'argumentB'));
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(1));
        $this->assertSame('argumentB', call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertSame('argumentC', call_user_func($this->subject, 'argumentB', 'argumentC', 'argumentD'));
        $this->assertNull(call_user_func($this->subject, 'argumentA'));
        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(-1));
        $this->assertSame('argumentC', call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertSame('argumentD', call_user_func($this->subject, 'argumentB', 'argumentC', 'argumentD'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testReturnsThis()
    {
        $this->assertSame($this->subject, $this->subject->returnsThis());
        $this->assertSame($this->thisValue, call_user_func($this->subject));

        $this->subject->setThisValue(null);

        $this->assertSame($this->subject, call_user_func($this->subject));
    }

    public function testThrows()
    {
        $this->assertSame($this->subject, $this->subject->throws());

        $thrownExceptions = array();
        for ($i = 0; $i < 2; $i++) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertEquals(array(new Exception(), new Exception()), $thrownExceptions);
    }

    public function testThrowsWithException()
    {
        $exceptionA = new Exception();
        $exceptionB = new Exception();
        $this->assertSame($this->subject, $this->subject->throws($exceptionA, $exceptionB));

        $thrownExceptions = array();
        for ($i = 0; $i < 3; $i++) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertSame(array($exceptionA, $exceptionB, $exceptionB), $thrownExceptions);
    }

    public function testMultipleRules()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->returns('valueA')
                ->with('argumentA')->returns('valueB', 'valueC')->returns('valueD')
                ->with('argumentB')->returns('valueE', 'valueF')->throws()
        );

        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueA', call_user_func($this->subject));

        $this->assertSame('valueB', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueC', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueD', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueD', call_user_func($this->subject, 'argumentA'));

        $this->assertSame('valueE', call_user_func($this->subject, 'argumentB'));
        $this->assertSame('valueF', call_user_func($this->subject, 'argumentB'));
        $thrownExceptions = array();
        for ($i = 0; $i < 2; $i++) {
            try {
                call_user_func($this->subject, 'argumentB');
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }
        $this->assertEquals(array(new Exception(), new Exception()), $thrownExceptions);

        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueD', call_user_func($this->subject, 'argumentA'));
        $thrownExceptions = array();
        try {
            call_user_func($this->subject, 'argumentB');
        } catch (Exception $thrownException) {
            $thrownExceptions[] = $thrownException;
        }
        $this->assertEquals(array(new Exception()), $thrownExceptions);

        $this->assertSame(
            $this->subject,
            $this->subject
                ->with()->returns('valueB')
                ->with('argumentA')->returns('valueC')
                ->with('argumentB')->returns('valueE')
        );

        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));

        $this->assertSame('valueC', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueC', call_user_func($this->subject, 'argumentA'));

        $this->assertSame('valueE', call_user_func($this->subject, 'argumentB'));
        $this->assertSame('valueE', call_user_func($this->subject, 'argumentB'));
    }

    public function testDanglingRules()
    {
        $callCountA = 0;
        $callbackA = function () use (&$callCountA) {
            $callCountA++;
        };
        $callCountB = 0;
        $callbackB = function () use (&$callCountB) {
            $callCountB++;
        };

        $this->assertSame(
            $this->subject,
            $this->subject
                ->with('argumentA')
                ->with('argumentB')
                ->withExactly('argumentA')->calls($callbackA)
                ->withExactly('argumentB')->calls($callbackA)->calls($callbackB)
        );
        $this->assertNull(call_user_func($this->subject, 'argumentA'));
        $this->assertSame(1, $callCountA);
        $this->assertSame(0, $callCountB);
        $this->assertNull(call_user_func($this->subject, 'argumentB'));
        $this->assertSame(2, $callCountA);
        $this->assertSame(1, $callCountB);
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(2, $callCountA);
        $this->assertSame(1, $callCountB);
    }

    public function testInvokeMethods()
    {
        $this->assertNull($this->subject->invokeWith());
        $this->assertNull($this->subject->invoke());
        $this->assertNull(call_user_func($this->subject));
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'value';
        };
        $this->subject->does($callback);
        $value = null;
        $arguments = array(&$value);
        $this->subject->invokeWith($arguments);

        $this->assertSame('value', $value);
    }
}
