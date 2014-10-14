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

use Eloquent\Phony\Invocation\Invoker;
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
        $this->callback = 'implode';
        $this->self = (object) array();
        $this->id = 111;
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->subject = new Stub(
            $this->callback,
            $this->self,
            $this->id,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker
        );

        $this->wildcard = array(WildcardMatcher::instance());
        $this->callbackA = function () { return 'a'; };
        $this->callbackB = function () { return 'b'; };
        $this->callbackC = function () { return 'c'; };
        $this->callbackD = function () { return 'd'; };
        $this->callbackE = function () { return 'e'; };
        $this->callbackF = function () { return 'f'; };
    }

    public function testConstructor()
    {
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->self, $this->subject->self());
        $this->assertSame($this->id, $this->subject->id());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->invoker, $this->subject->invoker());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Stub();

        $this->assertTrue($this->subject->isAnonymous());
        $this->assertTrue(is_callable($this->subject->callback()));
        $this->assertNull(call_user_func($this->subject->callback()));
        $this->assertInstanceOf('Closure', $this->subject->self());
        $this->assertNull($this->subject->id());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
    }

    public function testSetSelf()
    {
        $this->subject->setSelf($this->subject);

        $this->assertSame($this->subject, $this->subject->self());

        $this->subject->setSelf($this->self);

        $this->assertSame($this->self, $this->subject->self());
    }

    public function testWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->with('a', new EqualToMatcher('b'))
                ->returns('x')
        );
        $this->assertSame('x', call_user_func($this->subject, 'a', 'b'));
        $this->assertSame('x', call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testWithExactly()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->withExactly('a', new EqualToMatcher('b'))
                ->returns('x')
        );
        $this->assertSame('x', call_user_func($this->subject, 'a', 'b'));
        $this->assertSame('x', call_user_func($this->subject, 'a', 'b'));
        $this->assertNull(call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testCalls()
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
                ->calls($callbackA, 'first')->returns()
                ->calls($callbackA, 'second')->calls($callbackB)->returns()
        );
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(
            array(
                array($this->self, 'first'),
            ),
            $callsA
        );
        $this->assertSame(0, $callCountB);
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(
            array(
                array($this->self, 'first'),
                array($this->self, 'second'),
            ),
            $callsA
        );
        $this->assertSame(1, $callCountB);
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(
            array(
                array($this->self, 'first'),
                array($this->self, 'second'),
                array($this->self, 'second'),
            ),
            $callsA
        );
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
        $this->assertNull(call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(
            array(
                array($this->self, 'first'),
            ),
            $callsA
        );
        $this->assertSame(0, $callCountB);
        $this->assertNull(call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(
            array(
                array($this->self, 'first'),
                array($this->self, 'second', 'a', 'b'),
            ),
            $callsA
        );
        $this->assertSame(1, $callCountB);
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame(
            array(
                array($this->self, 'first'),
                array($this->self, 'second', 'a', 'b'),
                array($this->self, 'second'),
            ),
            $callsA
        );
        $this->assertSame(2, $callCountB);
    }

    public function testCallsWithWithReferenceParameters()
    {
        $callback = function ($self, &$a, &$b) {
            $a = 'a';
            $b = 'b';
        };
        $value = null;
        $this->subject->callsWith($callback, array(&$value), true);
        $argument = null;
        $this->subject->invokeWith(array(&$argument));

        $this->assertSame('a', $value);
        $this->assertSame('b', $argument);
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
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'x'));
        $this->assertSame(
            array(
                array($this->self, 'first'),
            ),
            $callsA
        );
        $this->assertSame(array(), $callsB);
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'x'));
        $this->assertSame(
            array(
                array($this->self, 'first'),
                array($this->self),
            ),
            $callsA
        );
        $this->assertSame(
            array(
                array($this->self, 'second'),
            ),
            $callsB
        );
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'x'));
        $this->assertSame(
            array(
                array($this->self, 'first'),
                array($this->self),
                array($this->self),
            ),
            $callsA
        );
        $this->assertSame(
            array(
                array($this->self, 'second'),
                array($this->self, 'second'),
            ),
            $callsB
        );
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
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'x'));
        $this->assertSame(
            array(
                array($this->self, 'first', $callbackA, $callbackB, 'x'),
            ),
            $callsA
        );
        $this->assertSame(array(), $callsB);
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'x'));
        $this->assertSame(
            array(
                array($this->self, 'first', $callbackA, $callbackB, 'x'),
                array($this->self),
            ),
            $callsA
        );
        $this->assertSame(
            array(
                array($this->self, 'second'),
            ),
            $callsB
        );
        $this->assertNull(call_user_func($this->subject, $callbackA, $callbackB, 'x'));
        $this->assertSame(
            array(
                array($this->self, 'first', $callbackA, $callbackB, 'x'),
                array($this->self),
                array($this->self),
            ),
            $callsA
        );
        $this->assertSame(
            array(
                array($this->self, 'second'),
                array($this->self, 'second'),
            ),
            $callsB
        );
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
        $this->assertNull(call_user_func($this->subject, 'x'));
    }

    public function testCallsArgumentWithWithReferenceParameters()
    {
        $callback = function ($self, &$a, &$b) {
            $a = 'a';
            $b = 'b';
        };
        $value = null;
        $this->subject->callsArgumentWith(1, array(&$value), true);
        $argument = null;
        $this->subject->invokeWith(array(&$argument, $callback));

        $this->assertSame('a', $value);
        $this->assertSame('b', $argument);
    }

    public function testSetsArgument()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->setsArgument('a')
                ->setsArgument('b', 1)
                ->setsArgument('c', -1)
                ->setsArgument('d', 111)
        );

        $a = null;
        $b = null;
        $c = null;
        $this->subject->invokeWith(array(&$a, &$b, &$c));
        $this->subject->invokeWith();

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
    }

    public function testDoes()
    {
        $this->assertSame($this->subject, $this->subject->does($this->callbackA, $this->callbackB));
        $this->assertSame('a', call_user_func($this->subject));
        $this->assertSame('b', call_user_func($this->subject));
        $this->assertSame('b', call_user_func($this->subject));
    }

    public function testForwards()
    {
        $this->assertSame($this->subject, $this->subject->forwards());
        $this->assertSame('a, b', call_user_func($this->subject, ', ', array('a', 'b')));
    }

    public function testReturns()
    {
        $this->assertSame($this->subject, $this->subject->returns('a', 'b'));
        $this->assertSame('a', call_user_func($this->subject));
        $this->assertSame('b', call_user_func($this->subject));
        $this->assertSame('b', call_user_func($this->subject));
        $this->assertSame($this->subject, $this->subject->with()->returns());
        $this->assertNull(call_user_func($this->subject));
    }

    public function testReturnsArgument()
    {
        $this->assertSame($this->subject, $this->subject->returnsArgument());
        $this->assertSame('a', call_user_func($this->subject, 'a'));
        $this->assertSame('b', call_user_func($this->subject, 'b'));
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(1));
        $this->assertSame('b', call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertSame('c', call_user_func($this->subject, 'b', 'c', 'd'));
        $this->assertNull(call_user_func($this->subject, 'a'));
        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(-1));
        $this->assertSame('c', call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertSame('d', call_user_func($this->subject, 'b', 'c', 'd'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testReturnsSelf()
    {
        $this->assertSame($this->subject, $this->subject->returnsSelf());
        $this->assertSame($this->self, call_user_func($this->subject));

        $this->subject->setSelf($this);

        $this->assertSame($this, call_user_func($this->subject));
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
                ->returns('a')
                ->with('a')->returns('b', 'c')->returns('d')
                ->with('b')->returns('e', 'f')->throws()
        );

        $this->assertSame('a', call_user_func($this->subject));
        $this->assertSame('a', call_user_func($this->subject));

        $this->assertSame('b', call_user_func($this->subject, 'a'));
        $this->assertSame('c', call_user_func($this->subject, 'a'));
        $this->assertSame('d', call_user_func($this->subject, 'a'));
        $this->assertSame('d', call_user_func($this->subject, 'a'));

        $this->assertSame('e', call_user_func($this->subject, 'b'));
        $this->assertSame('f', call_user_func($this->subject, 'b'));
        $thrownExceptions = array();
        for ($i = 0; $i < 2; $i++) {
            try {
                call_user_func($this->subject, 'b');
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }
        $this->assertEquals(array(new Exception(), new Exception()), $thrownExceptions);

        $this->assertSame('a', call_user_func($this->subject));
        $this->assertSame('d', call_user_func($this->subject, 'a'));
        $thrownExceptions = array();
        try {
            call_user_func($this->subject, 'b');
        } catch (Exception $thrownException) {
            $thrownExceptions[] = $thrownException;
        }
        $this->assertEquals(array(new Exception()), $thrownExceptions);

        $this->assertSame(
            $this->subject,
            $this->subject
                ->with()->returns('b')
                ->with('a')->returns('c')
                ->with('b')->returns('e')
        );

        $this->assertSame('b', call_user_func($this->subject));
        $this->assertSame('b', call_user_func($this->subject));

        $this->assertSame('c', call_user_func($this->subject, 'a'));
        $this->assertSame('c', call_user_func($this->subject, 'a'));

        $this->assertSame('e', call_user_func($this->subject, 'b'));
        $this->assertSame('e', call_user_func($this->subject, 'b'));
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
                ->with('a')
                ->with('b')
                ->withExactly('a')->calls($callbackA)
                ->withExactly('b')->calls($callbackA)->calls($callbackB)
        );
        $this->assertNull(call_user_func($this->subject, 'a'));
        $this->assertSame(1, $callCountA);
        $this->assertSame(0, $callCountB);
        $this->assertNull(call_user_func($this->subject, 'b'));
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
        $callback = function ($self, &$argument) {
            $argument = 'x';
        };
        $this->subject->does($callback);
        $value = null;
        $arguments = array(&$value);
        $this->subject->invokeWith($arguments);

        $this->assertSame('x', $value);
    }
}
