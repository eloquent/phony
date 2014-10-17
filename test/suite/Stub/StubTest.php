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

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
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
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new Stub(
            $this->callback,
            $this->self,
            $this->id,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector
        );

        $this->callsA = array();
        $callsA = &$this->callsA;
        $this->callCountA = 0;
        $callCountA = &$this->callCountA;
        $this->callbackA = function () use (&$callsA, &$callCountA) {
            $arguments = func_get_args();
            $callsA[] = $arguments;
            $callCountA++;

            array_unshift($arguments, 'A');

            return $arguments;
        };

        $this->callsB = array();
        $callsB = &$this->callsB;
        $this->callCountB = 0;
        $callCountB = &$this->callCountB;
        $this->callbackB = function () use (&$callsB, &$callCountB) {
            $arguments = func_get_args();
            $callsB[] = $arguments;
            $callCountB++;

            array_unshift($arguments, 'B');

            return $arguments;
        };

        $this->callsC = array();
        $callsC = &$this->callsC;
        $this->callCountC = 0;
        $callCountC = &$this->callCountC;
        $this->callbackC = function () use (&$callsC, &$callCountC) {
            $arguments = func_get_args();
            $callsC[] = $arguments;
            $callCountC++;

            array_unshift($arguments, 'C');

            return $arguments;
        };

        $this->callsD = array();
        $callsD = &$this->callsD;
        $this->callCountD = 0;
        $callCountD = &$this->callCountD;
        $this->callbackD = function () use (&$callsD, &$callCountD) {
            $arguments = func_get_args();
            $callsD[] = $arguments;
            $callCountD++;

            array_unshift($arguments, 'D');

            return $arguments;
        };

        $this->callsE = array();
        $callsE = &$this->callsE;
        $this->callCountE = 0;
        $callCountE = &$this->callCountE;
        $this->callbackE = function () use (&$callsE, &$callCountE) {
            $arguments = func_get_args();
            $callsE[] = $arguments;
            $callCountE++;

            array_unshift($arguments, 'E');

            return $arguments;
        };

        $this->callsF = array();
        $callsF = &$this->callsF;
        $this->callCountF = 0;
        $callCountF = &$this->callCountF;
        $this->callbackF = function () use (&$callsF, &$callCountF) {
            $arguments = func_get_args();
            $callsF[] = $arguments;
            $callCountF++;

            array_unshift($arguments, 'F');

            return $arguments;
        };

        $this->referenceCallback = function (&$a, &$b = null, &$c = null, &$d = null) {
            $a = 'a';
            $b = 'b';
            $c = 'c';
            $d = 'd';
        };
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
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
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
        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
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
        $this->assertSame(
            $this->subject,
            $this->subject
                ->calls($this->callbackA)->returns()
                ->calls($this->callbackA, $this->callbackB)->calls($this->callbackC)->returns()
        );

        $this->assertNull(call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(
            array(
                array('a', 'b'),
            ),
            $this->callsA
        );
        $this->assertSame(array(), $this->callsB);
        $this->assertSame(array(), $this->callsC);

        $this->assertNull(call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(
            array(
                array('a', 'b'),
                array('c', 'd'),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array('c', 'd'),
            ),
            $this->callsB
        );
        $this->assertSame(
            array(
                array('c', 'd'),
            ),
            $this->callsC
        );

        $this->assertNull(call_user_func($this->subject, 'e', 'f'));
        $this->assertSame(
            array(
                array('a', 'b'),
                array('c', 'd'),
                array('e', 'f'),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array('c', 'd'),
                array('e', 'f'),
            ),
            $this->callsB
        );
        $this->assertSame(
            array(
                array('c', 'd'),
                array('e', 'f'),
            ),
            $this->callsC
        );
    }

    public function testCallsWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $this->subject->calls($this->referenceCallback);
        $this->subject->invokeWith(array(&$a, &$b));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testCallsWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsWith($this->callbackA, array('A', 'B'), true, true, true)
                ->returns()
                ->callsWith($this->callbackA, array('C', 'D'), true, true, true)
                ->callsWith($this->callbackB, array('E', 'F'), true, true, true)
                ->returns()
        );

        $this->assertNull(call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(
            array(
                array($this->self, 'A', 'B', array('a', 'b'), 'a', 'b'),
            ),
            $this->callsA
        );
        $this->assertSame(array(), $this->callsB);

        $this->assertNull(call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(
            array(
                array($this->self, 'A', 'B', array('a', 'b'), 'a', 'b'),
                array($this->self, 'C', 'D', array('c', 'd'), 'c', 'd'),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array($this->self, 'E', 'F', array('c', 'd'), 'c', 'd'),
            ),
            $this->callsB
        );

        $this->assertNull(call_user_func($this->subject, 'e', 'f'));
        $this->assertSame(
            array(
                array($this->self, 'A', 'B', array('a', 'b'), 'a', 'b'),
                array($this->self, 'C', 'D', array('c', 'd'), 'c', 'd'),
                array($this->self, 'C', 'D', array('e', 'f'), 'e', 'f'),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array($this->self, 'E', 'F', array('c', 'd'), 'c', 'd'),
                array($this->self, 'E', 'F', array('e', 'f'), 'e', 'f'),
            ),
            $this->callsB
        );
    }

    public function testCallsWithDefaults()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsWith($this->callbackA)->returns()
                ->callsWith($this->callbackA)->callsWith($this->callbackB)->returns()
        );

        $this->assertNull(call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(
            array(
                array('a', 'b'),
            ),
            $this->callsA
        );
        $this->assertSame(array(), $this->callsB);

        $this->assertNull(call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(
            array(
                array('a', 'b'),
                array('c', 'd'),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array('c', 'd'),
            ),
            $this->callsB
        );

        $this->assertNull(call_user_func($this->subject, 'e', 'f'));
        $this->assertSame(
            array(
                array('a', 'b'),
                array('c', 'd'),
                array('e', 'f'),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array('c', 'd'),
                array('e', 'f'),
            ),
            $this->callsB
        );
    }

    public function testCallsWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->callsWith($this->referenceCallback, array(&$a, &$b));
        $this->subject->invokeWith(array(&$c, &$d));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
    }

    public function testCallsArgument()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgument(111, -111)->returns()
                ->callsArgument(null)->returns()
                ->callsArgument(1)->callsArgument(2, null)->returns()
        );

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB, $this->callbackC));
        $this->assertSame(0, $this->callCountA);
        $this->assertSame(0, $this->callCountB);
        $this->assertSame(0, $this->callCountC);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB, $this->callbackC));
        $this->assertSame(1, $this->callCountA);
        $this->assertSame(0, $this->callCountB);
        $this->assertSame(0, $this->callCountC);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB, $this->callbackC));
        $this->assertSame(2, $this->callCountA);
        $this->assertSame(1, $this->callCountB);
        $this->assertSame(1, $this->callCountC);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB, $this->callbackC));
        $this->assertSame(3, $this->callCountA);
        $this->assertSame(2, $this->callCountB);
        $this->assertSame(2, $this->callCountC);

        $this->assertNull(call_user_func($this->subject, 'a', 'b', 'c'));
    }

    public function testCallsArgumentWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgumentWith(111)
                ->callsArgumentWith(-111)
                ->returns()
                ->callsArgumentWith(null, array('A', 'B'), true, true, true)
                ->returns()
                ->callsArgumentWith(0, array('C', 'D'), true, true, true)
                ->callsArgumentWith(1, array('E', 'F'), true, true, true)
                ->returns()
        );

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(array(), $this->callsA);
        $this->assertSame(array(), $this->callsB);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(
            array(
                array(
                    $this->self,
                    'A',
                    'B',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsA
        );
        $this->assertSame(array(), $this->callsB);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(
            array(
                array(
                    $this->self,
                    'A',
                    'B',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'C',
                    'D',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array(
                    $this->self,
                    'E',
                    'F',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsB
        );

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(
            array(
                array(
                    $this->self,
                    'A',
                    'B',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'C',
                    'D',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'C',
                    'D',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsA
        );
        $this->assertSame(
            array(
                array(
                    $this->self,
                    'E',
                    'F',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'E',
                    'F',
                    array($this->callbackA, $this->callbackB),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsB
        );

        $this->assertNull(call_user_func($this->subject, 'a', 'b'));
    }

    public function testCallsArgumentWithDefaults()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgumentWith(null)->returns()
                ->callsArgumentWith(0)->callsArgumentWith(1)->returns()
        );

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(1, $this->callCountA);
        $this->assertSame(0, $this->callCountB);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(2, $this->callCountA);
        $this->assertSame(1, $this->callCountB);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(3, $this->callCountA);
        $this->assertSame(2, $this->callCountB);
    }

    public function testCallsArgumentWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->callsArgumentWith(2, array(&$a, &$b), false, false, true);
        $this->subject->invokeWith(array(&$c, &$d, $this->referenceCallback));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
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
                ->setsArgument('e', -111)
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
        $this->assertSame(
            $this->subject,
            $this->subject
                ->does($this->callbackA)
                ->does($this->callbackB, $this->callbackC)
        );

        $this->assertSame(array('A', 'a', 'b'), call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(array('B', 'c', 'd'), call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(array('C', 'e', 'f'), call_user_func($this->subject, 'e', 'f'));
        $this->assertSame(array('C', 'g', 'h'), call_user_func($this->subject, 'g', 'h'));
    }

    public function testDoesWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $this->subject->does($this->referenceCallback);
        $this->subject->invokeWith(array(&$a, &$b));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testDoesWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->doesWith($this->callbackA, array(1, 2), true, true, true)
                ->doesWith($this->callbackB, array(3, 4), true, true, true)
        );

        $this->assertSame(
            array('A', $this->self, 1, 2, array('a', 'b'), 'a', 'b'),
            call_user_func($this->subject, 'a', 'b')
        );
        $this->assertSame(
            array('B', $this->self, 3, 4, array('c', 'd'), 'c', 'd'),
            call_user_func($this->subject, 'c', 'd')
        );
        $this->assertSame(
            array('B', $this->self, 3, 4, array('e', 'f'), 'e', 'f'),
            call_user_func($this->subject, 'e', 'f')
        );
    }

    public function testDoesWithDefaults()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->doesWith($this->callbackA)
                ->doesWith($this->callbackB)
        );

        $this->assertSame(array('A', 'a', 'b'), call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(array('B', 'c', 'd'), call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(array('B', 'e', 'f'), call_user_func($this->subject, 'e', 'f'));
    }

    public function testDoesWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->doesWith($this->referenceCallback, array(&$a, &$b));
        $this->subject->invokeWith(array(&$c, &$d));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
    }

    public function testForwards()
    {
        $this->subject = new Stub($this->callbackA, $this->self);

        $this->assertSame($this->subject, $this->subject->forwards(array(1, 2), true, true, true));
        $this->assertSame(
            array('A', $this->self, 1, 2, array('a', 'b'), 'a', 'b'),
            call_user_func($this->subject, 'a', 'b')
        );
        $this->assertSame(
            array('A', $this->self, 1, 2, array('c', 'd'), 'c', 'd'),
            call_user_func($this->subject, 'c', 'd')
        );
    }

    public function testForwardsDefaults()
    {
        $this->assertSame($this->subject, $this->subject->forwards());
        $this->assertSame('a, b', call_user_func($this->subject, ', ', array('a', 'b')));
        $this->assertSame('c - d', call_user_func($this->subject, ' - ', array('c', 'd')));
    }

    public function forwardsSelfParameterAutoDetectionoDetectionData()
    {
        return array(
            'Exact match' => array(
                function (TestClassA $self) {
                    return func_get_args();
                },
                new TestClassA(),
                array('a', 'b'),
                array(new TestClassA(), 'a', 'b'),
            ),
            'Subclass' => array(
                function (TestClassA $self) {
                    return func_get_args();
                },
                new TestClassB(),
                array('a', 'b'),
                array(new TestClassB(), 'a', 'b'),
            ),
            'Superclass' => array(
                function (TestClassB $self) {
                    return func_get_args();
                },
                new TestClassA(),
                array(new TestClassB(), 'a', 'b'),
                array(new TestClassB(), 'a', 'b'),
            ),
            'No hint' => array(
                function ($self) {
                    return func_get_args();
                },
                new TestClassA(),
                array('a', 'b'),
                array('a', 'b'),
            ),
            'Wrong name' => array(
                function (TestClassA $a) {
                    return func_get_args();
                },
                new TestClassA(),
                array(new TestClassA(), 'a', 'b'),
                array(new TestClassA(), 'a', 'b'),
            ),
            'No parameters' => array(
                function () {
                    return func_get_args();
                },
                new TestClassA(),
                array('a', 'b'),
                array('a', 'b'),
            ),
            'Not a closure' => array(
                'implode',
                new TestClassA(),
                array(array('a', 'b')),
                'ab',
            ),
            'Self is not object' => array(
                function (TestClassA $self) {
                    return func_get_args();
                },
                'Eloquent\Phony\Test\TestClassA',
                array(new TestClassA(), 'a', 'b'),
                array(new TestClassA(), 'a', 'b'),
            ),
        );
    }

    /**
     * @dataProvider forwardsSelfParameterAutoDetectionoDetectionData
     */
    public function testForwardsSelfParameterAutoDetection($callback, $self, $arguments, $expected)
    {
        $subject = new Stub($callback, $self);
        $subject->forwards();

        $this->assertEquals($expected, call_user_func_array($subject, $arguments));
    }

    public function testForwardsWithReferenceParameters()
    {
        $this->subject = new Stub($this->referenceCallback, $this->self);
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->forwards(array(&$a, &$b));
        $this->subject->invokeWith(array(&$c, &$d));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
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

        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(111));
        $this->assertNull(call_user_func($this->subject, 'a'));

        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(-111));
        $this->assertNull(call_user_func($this->subject, 'a'));
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
                ->withExactly('b')->calls($callbackA, $callbackB)
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
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->doesWith($this->referenceCallback, array(&$a, &$b));
        $this->subject->invokeWith(array(&$c, &$d));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
    }
}
