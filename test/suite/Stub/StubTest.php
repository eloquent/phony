<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Closure;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Phpunit\Phony;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Exception;
use PHPUnit_Framework_TestCase;
use stdClass;

class StubTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->self = (object) array();
        $this->label = 'label';
        $this->defaultAnswerCallback = function ($stub) {
            $stub->returns('default answer');
        };
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->invocableInspector = new InvocableInspector();
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->subject = new Stub(
            $this->callback,
            $this->self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );

        $this->callsA = array();
        $callsA = &$this->callsA;
        $this->callCountA = 0;
        $callCountA = &$this->callCountA;
        $this->callbackA = function () use (&$callsA, &$callCountA) {
            $arguments = func_get_args();
            $callsA[] = $arguments;
            ++$callCountA;

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
            ++$callCountB;

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
            ++$callCountC;

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
            ++$callCountD;

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
            ++$callCountE;

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
            ++$callCountF;

            array_unshift($arguments, 'F');

            return $arguments;
        };

        $this->referenceCallback = function (&$a, &$b = null, &$c = null, &$d = null) {
            $a = 'a';
            $b = 'b';
            $c = 'c';
            $d = 'd';
        };

        $this->featureDetector = FeatureDetector::instance();
    }

    public function testConstructor()
    {
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->self, $this->subject->self());
        $this->assertSame($this->label, $this->subject->label());
        $this->assertSame($this->defaultAnswerCallback, $this->subject->defaultAnswerCallback());
    }

    public function testSetSelf()
    {
        $this->assertSame($this->subject, $this->subject->setSelf($this->subject));
        $this->assertSame($this->subject, $this->subject->self());
        $this->assertSame($this->subject, $this->subject->setSelf($this->self));
        $this->assertSame($this->self, $this->subject->self());
    }

    public function testSetDefaultAnswerCallback()
    {
        $callbackA = function () {};
        $callbackB = function () {};

        $this->assertSame($this->subject, $this->subject->setDefaultAnswerCallback($callbackA));
        $this->assertSame($callbackA, $this->subject->defaultAnswerCallback());
        $this->assertSame($this->subject, $this->subject->setDefaultAnswerCallback($callbackB));
        $this->assertSame($callbackB, $this->subject->defaultAnswerCallback());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(null));
        $this->assertNull($this->subject->label());

        $this->subject->setLabel($this->label);

        $this->assertSame($this->label, $this->subject->label());
    }

    public function testWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->returns()
                ->with('a', new EqualToMatcher('b'))
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
        $this->subject->calls($this->referenceCallback)->returns();
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
        $this->assertEquals(
            array(
                array($this->self, 'A', 'B', new Arguments(array('a', 'b')), 'a', 'b'),
            ),
            $this->callsA
        );
        $this->assertSame(array(), $this->callsB);

        $this->assertNull(call_user_func($this->subject, 'c', 'd'));
        $this->assertEquals(
            array(
                array($this->self, 'A', 'B', new Arguments(array('a', 'b')), 'a', 'b'),
                array($this->self, 'C', 'D', new Arguments(array('c', 'd')), 'c', 'd'),
            ),
            $this->callsA
        );
        $this->assertEquals(
            array(
                array($this->self, 'E', 'F', new Arguments(array('c', 'd')), 'c', 'd'),
            ),
            $this->callsB
        );

        $this->assertNull(call_user_func($this->subject, 'e', 'f'));
        $this->assertEquals(
            array(
                array($this->self, 'A', 'B', new Arguments(array('a', 'b')), 'a', 'b'),
                array($this->self, 'C', 'D', new Arguments(array('c', 'd')), 'c', 'd'),
                array($this->self, 'C', 'D', new Arguments(array('e', 'f')), 'e', 'f'),
            ),
            $this->callsA
        );
        $this->assertEquals(
            array(
                array($this->self, 'E', 'F', new Arguments(array('c', 'd')), 'c', 'd'),
                array($this->self, 'E', 'F', new Arguments(array('e', 'f')), 'e', 'f'),
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

    public function testCallsWithSelfParameterAutoDetection()
    {
        $self = (object) array();
        $subject = new Stub(
            null,
            $self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );

        $actual = null;
        $subject->callsWith(
                function ($phonySelf) use (&$actual) {
                    $actual = func_get_args();
                }
            )
            ->returns();
        $subject('a', 'b');

        $this->assertSame(array($self, 'a', 'b'), $actual);
    }

    public function testCallsWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->callsWith($this->referenceCallback, array(&$a, &$b))->returns();
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
                ->callsArgument()->returns()
                ->callsArgument(1)->callsArgument(2, 0)->returns()
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
                ->callsArgumentWith(0, array('A', 'B'), true, true, true)
                ->returns()
                ->callsArgumentWith(0, array('C', 'D'), true, true, true)
                ->callsArgumentWith(1, array('E', 'F'), true, true, true)
                ->returns()
        );

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(array(), $this->callsA);
        $this->assertSame(array(), $this->callsB);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertEquals(
            array(
                array(
                    $this->self,
                    'A',
                    'B',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsA
        );
        $this->assertSame(array(), $this->callsB);

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertEquals(
            array(
                array(
                    $this->self,
                    'A',
                    'B',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'C',
                    'D',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsA
        );
        $this->assertEquals(
            array(
                array(
                    $this->self,
                    'E',
                    'F',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsB
        );

        $this->assertNull(call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertEquals(
            array(
                array(
                    $this->self,
                    'A',
                    'B',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'C',
                    'D',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'C',
                    'D',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
            ),
            $this->callsA
        );
        $this->assertEquals(
            array(
                array(
                    $this->self,
                    'E',
                    'F',
                    new Arguments(array($this->callbackA, $this->callbackB)),
                    $this->callbackA,
                    $this->callbackB,
                ),
                array(
                    $this->self,
                    'E',
                    'F',
                    new Arguments(array($this->callbackA, $this->callbackB)),
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
                ->callsArgumentWith()->returns()
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
        $this->subject->callsArgumentWith(2, array(&$a, &$b), false, false, true)->returns();
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
                ->setsArgument(1, 'b')
                ->setsArgument(-1, 'c')
                ->setsArgument(111, 'd')
                ->setsArgument(-111, 'e')
                ->returns()
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

    public function testSetsArgumentWithInstanceHandles()
    {
        $adaptable = Phony::mock();
        $unadaptable = Phony::mock()->setIsAdaptable(false);
        $this->subject->setsArgument(0, $adaptable)->setsArgument(1, $unadaptable);

        $a = null;
        $b = null;
        $this->subject->invokeWith(array(&$a, &$b));

        $this->assertSame($adaptable->mock(), $a);
        $this->assertSame($unadaptable, $b);
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

        $this->assertEquals(
            array('A', $this->self, 1, 2, new Arguments(array('a', 'b')), 'a', 'b'),
            call_user_func($this->subject, 'a', 'b')
        );
        $this->assertEquals(
            array('B', $this->self, 3, 4, new Arguments(array('c', 'd')), 'c', 'd'),
            call_user_func($this->subject, 'c', 'd')
        );
        $this->assertEquals(
            array('B', $this->self, 3, 4, new Arguments(array('e', 'f')), 'e', 'f'),
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

    public function testDoesWithSelfParameterAutoDetection()
    {
        $self = (object) array();
        $subject = new Stub(
            null,
            $self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
        $actual = null;
        $subject->doesWith(
            function ($phonySelf) use (&$actual) {
                $actual = func_get_args();
            }
        );
        $subject('a', 'b');

        $this->assertSame(array($self, 'a', 'b'), $actual);
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
        $this->subject = new Stub(
            $this->callbackA,
            $this->self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );

        $this->assertSame($this->subject, $this->subject->forwards(array(1, 2), true, true, true));
        $this->assertEquals(
            array('A', $this->self, 1, 2, new Arguments(array('a', 'b')), 'a', 'b'),
            call_user_func($this->subject, 'a', 'b')
        );
        $this->assertEquals(
            array('A', $this->self, 1, 2, new Arguments(array('c', 'd')), 'c', 'd'),
            call_user_func($this->subject, 'c', 'd')
        );
    }

    public function testForwardsDefaults()
    {
        $this->assertSame($this->subject, $this->subject->forwards());
        $this->assertSame('a, b', call_user_func($this->subject, ', ', array('a', 'b')));
        $this->assertSame('c - d', call_user_func($this->subject, ' - ', array('c', 'd')));
    }

    public function forwardsSelfParameterAutoDetectionData()
    {
        return array(
            'Exact match' => array(
                function (TestClassA $phonySelf) {
                    return func_get_args();
                },
                new TestClassA(),
                array('a', 'b'),
                array(new TestClassA(), 'a', 'b'),
            ),
            'Subclass' => array(
                function (TestClassA $phonySelf) {
                    return func_get_args();
                },
                new TestClassB(),
                array('a', 'b'),
                array(new TestClassB(), 'a', 'b'),
            ),
            'No hint' => array(
                function ($phonySelf) {
                    return func_get_args();
                },
                new TestClassA(),
                array('a', 'b'),
                array(new TestClassA(), 'a', 'b'),
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
            'Not a callable object' => array(
                'implode',
                new TestClassA(),
                array(array('a', 'b')),
                'ab',
            ),
        );
    }

    /**
     * @dataProvider forwardsSelfParameterAutoDetectionData
     */
    public function testForwardsSelfParameterAutoDetection($callback, $self, $arguments, $expected)
    {
        $subject = new Stub(
            $callback,
            $self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
        $subject->forwards();

        $this->assertEquals($expected, call_user_func_array($subject, $arguments));
    }

    public function testForwardsWithReferenceParameters()
    {
        $this->subject = new Stub(
            $this->referenceCallback,
            $this->self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
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
        $this->assertSame($this->subject, $this->subject->returns());
        $this->assertNull(call_user_func($this->subject));
    }

    public function returnsWithReturnTypeData()
    {
        return array(
            'bool'   => array('bool',   false),
            'int'    => array('int',    0),
            'float'  => array('float',  .0),
            'string' => array('string', ''),
            'array'  => array('array',  array()),
        );
    }

    /**
     * @dataProvider returnsWithReturnTypeData
     */
    public function testReturnsWithReturnTypes($type, $expected)
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $this->subject = new Stub(
            eval("return function (): $type {};"),
            null,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
        $this->subject->returns();

        $this->assertSame($expected, call_user_func($this->subject));
    }

    public function testReturnsWithStdClassReturnType()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $this->subject = new Stub(
            eval('return function (): stdClass {};'),
            null,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
        $this->subject->returns();

        $this->assertInstanceOf('stdClass', call_user_func($this->subject));
    }

    public function testReturnsWithCallableReturnType()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $this->subject = new Stub(
            eval('return function (): callable {};'),
            null,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
        $this->subject->returns();

        $this->assertInstanceOf('Closure', call_user_func($this->subject));
    }

    public function returnsWithTraversableReturnTypeData()
    {
        return array(
            'Generator' => array('Generator'),
            'Iterator' => array('Iterator'),
            'Traversable' => array('Traversable'),
        );
    }

    /**
     * @dataProvider returnsWithTraversableReturnTypeData
     */
    public function testReturnsWithTraversableReturnType($type)
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $this->subject = new Stub(
            eval("return function (): $type {};"),
            null,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
        $this->subject->returns();

        $result = call_user_func($this->subject);

        $this->assertInstanceOf($type, $result);
        $this->assertSame(array(), iterator_to_array($result));
    }

    public function testReturnsWithClassReturnTypeFailure()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $this->subject = new Stub(
            eval('return function (): Throwable {};'),
            null,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );

        $this->setExpectedException('InvalidArgumentException');
        $this->subject->returns();
    }

    public function testReturnsWithInstanceHandles()
    {
        $adaptable = Phony::mock();
        $unadaptable = Phony::mock()->setIsAdaptable(false);
        $this->subject->returns($adaptable, $unadaptable);

        $this->assertSame($adaptable->mock(), call_user_func($this->subject));
        $this->assertSame($unadaptable, call_user_func($this->subject));
    }

    public function testReturnsArgument()
    {
        $this->assertSame($this->subject, $this->subject->returnsArgument());
        $this->assertSame('a', call_user_func($this->subject, 'a'));
        $this->assertSame('b', call_user_func($this->subject, 'b'));
        $this->assertNull(call_user_func($this->subject));

        $this->assertSame($this->subject, $this->subject->returnsArgument(1));
        $this->assertSame('b', call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertSame('c', call_user_func($this->subject, 'b', 'c', 'd'));
        $this->assertNull(call_user_func($this->subject, 'a'));

        $this->assertSame($this->subject, $this->subject->returnsArgument(-1));
        $this->assertSame('c', call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertSame('d', call_user_func($this->subject, 'b', 'c', 'd'));
        $this->assertNull(call_user_func($this->subject));

        $this->assertSame($this->subject, $this->subject->returnsArgument(111));
        $this->assertNull(call_user_func($this->subject, 'a'));

        $this->assertSame($this->subject, $this->subject->returnsArgument(-111));
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
        for ($i = 0; $i < 2; ++$i) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertEquals(array(new Exception(), new Exception()), $thrownExceptions);
    }

    public function testThrowsWithInstanceHandles()
    {
        $adaptable = Phony::mock('RuntimeException');
        $this->subject->throws($adaptable);

        $this->setExpectedException('RuntimeException');
        call_user_func($this->subject);
    }

    public function testThrowsWithException()
    {
        $exceptionA = new Exception();
        $exceptionB = new Exception();
        $this->assertSame($this->subject, $this->subject->throws($exceptionA, $exceptionB));

        $thrownExceptions = array();
        for ($i = 0; $i < 3; ++$i) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertSame(array($exceptionA, $exceptionB, $exceptionB), $thrownExceptions);
    }

    public function testThrowsWithMessage()
    {
        $this->assertSame($this->subject, $this->subject->throws('a', 'b'));

        $thrownExceptions = array();
        for ($i = 0; $i < 3; ++$i) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertEquals(array(new Exception('a'), new Exception('b'), new Exception('b')), $thrownExceptions);
    }

    public function testMultipleRules()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->returns('a')
                ->with('a', '*')->returns('b', 'c')->returns('d')
                ->with('b', '*')->returns('e', 'f')->throws()
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
        for ($i = 0; $i < 2; ++$i) {
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
                ->with()
                ->returns('b')
                ->with('a', '*')->returns('c')
                ->with('b', '*')->returns('e')
        );

        $this->assertSame('b', call_user_func($this->subject));
        $this->assertSame('b', call_user_func($this->subject));

        $this->assertSame('c', call_user_func($this->subject, 'a'));
        $this->assertSame('c', call_user_func($this->subject, 'a'));

        $this->assertSame('e', call_user_func($this->subject, 'b'));
        $this->assertSame('e', call_user_func($this->subject, 'b'));
    }

    public function testCloseRule()
    {
        $this->subject->returns('a');
        $this->subject->closeRule();
        $this->subject->returns('b');

        $this->assertSame('b', call_user_func($this->subject));
    }

    public function testCloseRuleFailureDanglingCriteria()
    {
        $this->subject->with();

        $this->setExpectedException('Eloquent\Phony\Stub\Exception\UnusedStubCriteriaException');
        $this->subject->closeRule();
    }

    public function testDanglingRules()
    {
        $callCountA = 0;
        $callbackA = function () use (&$callCountA) {
            ++$callCountA;
        };
        $callCountB = 0;
        $callbackB = function () use (&$callCountB) {
            ++$callCountB;
        };

        $this->assertSame(
            $this->subject,
            $this->subject
                ->with(array('a', 'b'))->calls($callbackA)
                ->with(array('c', 'd'))->calls($callbackA, $callbackB)
        );
        $this->assertSame('default answer', call_user_func($this->subject, array('a', 'b')));
        $this->assertSame(1, $callCountA);
        $this->assertSame(0, $callCountB);
        $this->assertSame('default answer', call_user_func($this->subject, array('c', 'd')));
        $this->assertSame(2, $callCountA);
        $this->assertSame(1, $callCountB);
        $this->assertSame('default answer', call_user_func($this->subject, array('e', 'f')));
        $this->assertSame(2, $callCountA);
        $this->assertSame(1, $callCountB);
    }

    public function testInvokeMethods()
    {
        $this->subject->returns();

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

    public function testInvokeWithNoRules()
    {
        $stub = new Stub(
            null,
            null,
            null,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );

        $this->assertSame('default answer', $stub());
    }
}
