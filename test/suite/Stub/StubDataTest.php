<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Exception\UnusedStubCriteriaException;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StubDataTest extends TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->self = (object) [];
        $this->label = 'label';
        $this->defaultAnswerCallback = function ($stub) {
            $stub->returns('default answer');
        };
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = FeatureDetector::instance();
        $this->emptyValueFactory = new EmptyValueFactory($this->featureDetector);
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->subject = new StubData(
            $this->callback,
            $this->self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );

        $this->emptyValueFactory->setStubVerifierFactory(StubVerifierFactory::instance());
        $this->emptyValueFactory->setMockBuilderFactory(MockBuilderFactory::instance());

        $this->callsA = [];
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

        $this->callsB = [];
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

        $this->callsC = [];
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

        $this->callsD = [];
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

        $this->callsE = [];
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

        $this->callsF = [];
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
                ->with('a', $this->matcherFactory->equalTo('b'))
                ->returns('x')
        );
        $this->assertSame('x', call_user_func($this->subject, 'a', 'b'));
        $this->assertSame('x', call_user_func($this->subject, 'a', 'b'));
        $this->assertSame('', (string) call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertSame('', (string) call_user_func($this->subject));
    }

    public function testCalls()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->calls($this->callbackA)->returns()
                ->calls($this->callbackA, $this->callbackB)->calls($this->callbackC)->returns()
        );

        $this->assertSame('', (string) call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(
            [
                ['a', 'b'],
            ],
            $this->callsA
        );
        $this->assertSame([], $this->callsB);
        $this->assertSame([], $this->callsC);

        $this->assertSame('', (string) call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(
            [
                ['a', 'b'],
                ['c', 'd'],
            ],
            $this->callsA
        );
        $this->assertSame(
            [
                ['c', 'd'],
            ],
            $this->callsB
        );
        $this->assertSame(
            [
                ['c', 'd'],
            ],
            $this->callsC
        );

        $this->assertSame('', (string) call_user_func($this->subject, 'e', 'f'));
        $this->assertSame(
            [
                ['a', 'b'],
                ['c', 'd'],
                ['e', 'f'],
            ],
            $this->callsA
        );
        $this->assertSame(
            [
                ['c', 'd'],
                ['e', 'f'],
            ],
            $this->callsB
        );
        $this->assertSame(
            [
                ['c', 'd'],
                ['e', 'f'],
            ],
            $this->callsC
        );
    }

    public function testCallsWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $this->subject->calls($this->referenceCallback)->returns();
        $this->subject->invokeWith([&$a, &$b]);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testCallsWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsWith($this->callbackA, ['A', 'B'], true, true, true)
                ->returns()
                ->callsWith($this->callbackA, ['C', 'D'], true, true, true)
                ->callsWith($this->callbackB, ['E', 'F'], true, true, true)
                ->returns()
        );

        $this->assertSame('', (string) call_user_func($this->subject, 'a', 'b'));
        $this->assertEquals(
            [
                [$this->self, 'A', 'B', new Arguments(['a', 'b']), 'a', 'b'],
            ],
            $this->callsA
        );
        $this->assertSame([], $this->callsB);

        $this->assertSame('', (string) call_user_func($this->subject, 'c', 'd'));
        $this->assertEquals(
            [
                [$this->self, 'A', 'B', new Arguments(['a', 'b']), 'a', 'b'],
                [$this->self, 'C', 'D', new Arguments(['c', 'd']), 'c', 'd'],
            ],
            $this->callsA
        );
        $this->assertEquals(
            [
                [$this->self, 'E', 'F', new Arguments(['c', 'd']), 'c', 'd'],
            ],
            $this->callsB
        );

        $this->assertSame('', (string) call_user_func($this->subject, 'e', 'f'));
        $this->assertEquals(
            [
                [$this->self, 'A', 'B', new Arguments(['a', 'b']), 'a', 'b'],
                [$this->self, 'C', 'D', new Arguments(['c', 'd']), 'c', 'd'],
                [$this->self, 'C', 'D', new Arguments(['e', 'f']), 'e', 'f'],
            ],
            $this->callsA
        );
        $this->assertEquals(
            [
                [$this->self, 'E', 'F', new Arguments(['c', 'd']), 'c', 'd'],
                [$this->self, 'E', 'F', new Arguments(['e', 'f']), 'e', 'f'],
            ],
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

        $this->assertSame('', (string) call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(
            [
                ['a', 'b'],
            ],
            $this->callsA
        );
        $this->assertSame([], $this->callsB);

        $this->assertSame('', (string) call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(
            [
                ['a', 'b'],
                ['c', 'd'],
            ],
            $this->callsA
        );
        $this->assertSame(
            [
                ['c', 'd'],
            ],
            $this->callsB
        );

        $this->assertSame('', (string) call_user_func($this->subject, 'e', 'f'));
        $this->assertSame(
            [
                ['a', 'b'],
                ['c', 'd'],
                ['e', 'f'],
            ],
            $this->callsA
        );
        $this->assertSame(
            [
                ['c', 'd'],
                ['e', 'f'],
            ],
            $this->callsB
        );
    }

    public function testCallsWithSelfParameterAutoDetection()
    {
        $self = (object) [];
        $subject = new StubData(
            null,
            $self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
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

        $this->assertSame([$self, 'a', 'b'], $actual);
    }

    public function testCallsWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->callsWith($this->referenceCallback, [&$a, &$b])->returns();
        $this->subject->invokeWith([&$c, &$d]);

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
                ->callsArgument()->returns()
                ->callsArgument(1)->callsArgument(2, 0)->returns()
        );

        $this->assertSame(
            '',
            (string) call_user_func($this->subject, $this->callbackA, $this->callbackB, $this->callbackC)
        );
        $this->assertSame(1, $this->callCountA);
        $this->assertSame(0, $this->callCountB);
        $this->assertSame(0, $this->callCountC);

        $this->assertSame(
            '',
            (string) call_user_func($this->subject, $this->callbackA, $this->callbackB, $this->callbackC)
        );
        $this->assertSame(2, $this->callCountA);
        $this->assertSame(1, $this->callCountB);
        $this->assertSame(1, $this->callCountC);

        $this->assertSame(
            '',
            (string) call_user_func($this->subject, $this->callbackA, $this->callbackB, $this->callbackC)
        );
        $this->assertSame(3, $this->callCountA);
        $this->assertSame(2, $this->callCountB);
        $this->assertSame(2, $this->callCountC);
    }

    public function testCallsArgumentWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgumentWith(0, ['A', 'B'], true, true, true)
                ->returns()
                ->callsArgumentWith(0, ['C', 'D'], true, true, true)
                ->callsArgumentWith(1, ['E', 'F'], true, true, true)
                ->returns()
        );

        $this->assertSame('', (string) call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertEquals(
            [
                [
                    $this->self,
                    'A',
                    'B',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
            ],
            $this->callsA
        );
        $this->assertSame([], $this->callsB);

        $this->assertSame('', (string) call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertEquals(
            [
                [
                    $this->self,
                    'A',
                    'B',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
                [
                    $this->self,
                    'C',
                    'D',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
            ],
            $this->callsA
        );
        $this->assertEquals(
            [
                [
                    $this->self,
                    'E',
                    'F',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
            ],
            $this->callsB
        );

        $this->assertSame('', (string) call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertEquals(
            [
                [
                    $this->self,
                    'A',
                    'B',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
                [
                    $this->self,
                    'C',
                    'D',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
                [
                    $this->self,
                    'C',
                    'D',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
            ],
            $this->callsA
        );
        $this->assertEquals(
            [
                [
                    $this->self,
                    'E',
                    'F',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
                [
                    $this->self,
                    'E',
                    'F',
                    new Arguments([$this->callbackA, $this->callbackB]),
                    $this->callbackA,
                    $this->callbackB,
                ],
            ],
            $this->callsB
        );
    }

    public function testCallsArgumentWithDefaults()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgumentWith()->returns()
                ->callsArgumentWith(0)->callsArgumentWith(1)->returns()
        );

        $this->assertSame('', (string) call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(1, $this->callCountA);
        $this->assertSame(0, $this->callCountB);

        $this->assertSame('', (string) call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(2, $this->callCountA);
        $this->assertSame(1, $this->callCountB);

        $this->assertSame('', (string) call_user_func($this->subject, $this->callbackA, $this->callbackB));
        $this->assertSame(3, $this->callCountA);
        $this->assertSame(2, $this->callCountB);
    }

    public function testCallsArgumentWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->callsArgumentWith(2, [&$a, &$b], false, false, true)->returns();
        $this->subject->invokeWith([&$c, &$d, $this->referenceCallback]);

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
                ->returns()
        );

        $a = null;
        $b = null;
        $c = null;
        $this->subject->invokeWith([&$a, &$b, &$c]);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
    }

    public function testSetsArgumentWithInstanceHandles()
    {
        $handle = Phony::mock();
        $this->subject->setsArgument(0, $handle);

        $a = null;
        $this->subject->invokeWith([&$a]);

        $this->assertSame($handle->get(), $a);
    }

    public function testDoes()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->does($this->callbackA)
                ->does($this->callbackB, $this->callbackC)
        );

        $this->assertSame(['A', 'a', 'b'], call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(['B', 'c', 'd'], call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(['C', 'e', 'f'], call_user_func($this->subject, 'e', 'f'));
        $this->assertSame(['C', 'g', 'h'], call_user_func($this->subject, 'g', 'h'));
    }

    public function testDoesWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $this->subject->does($this->referenceCallback);
        $this->subject->invokeWith([&$a, &$b]);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testDoesWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->doesWith($this->callbackA, [1, 2], true, true, true)
                ->doesWith($this->callbackB, [3, 4], true, true, true)
        );

        $this->assertEquals(
            ['A', $this->self, 1, 2, new Arguments(['a', 'b']), 'a', 'b'],
            call_user_func($this->subject, 'a', 'b')
        );
        $this->assertEquals(
            ['B', $this->self, 3, 4, new Arguments(['c', 'd']), 'c', 'd'],
            call_user_func($this->subject, 'c', 'd')
        );
        $this->assertEquals(
            ['B', $this->self, 3, 4, new Arguments(['e', 'f']), 'e', 'f'],
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

        $this->assertSame(['A', 'a', 'b'], call_user_func($this->subject, 'a', 'b'));
        $this->assertSame(['B', 'c', 'd'], call_user_func($this->subject, 'c', 'd'));
        $this->assertSame(['B', 'e', 'f'], call_user_func($this->subject, 'e', 'f'));
    }

    public function testDoesWithSelfParameterAutoDetection()
    {
        $self = (object) [];
        $subject = new StubData(
            null,
            $self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );
        $actual = null;
        $subject->doesWith(
            function ($phonySelf) use (&$actual) {
                $actual = func_get_args();
            }
        );
        $subject('a', 'b');

        $this->assertSame([$self, 'a', 'b'], $actual);
    }

    public function testDoesWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->doesWith($this->referenceCallback, [&$a, &$b]);
        $this->subject->invokeWith([&$c, &$d]);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
    }

    public function testForwards()
    {
        $this->subject = new StubData(
            $this->callbackA,
            $this->self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );

        $this->assertSame($this->subject, $this->subject->forwards([1, 2], true, true, true));
        $this->assertEquals(
            ['A', $this->self, 1, 2, new Arguments(['a', 'b']), 'a', 'b'],
            call_user_func($this->subject, 'a', 'b')
        );
        $this->assertEquals(
            ['A', $this->self, 1, 2, new Arguments(['c', 'd']), 'c', 'd'],
            call_user_func($this->subject, 'c', 'd')
        );
    }

    public function testForwardsDefaults()
    {
        $this->assertSame($this->subject, $this->subject->forwards());
        $this->assertSame('a, b', call_user_func($this->subject, ', ', ['a', 'b']));
        $this->assertSame('c - d', call_user_func($this->subject, ' - ', ['c', 'd']));
    }

    public function forwardsSelfParameterAutoDetectionData()
    {
        return [
            'Exact match' => [
                function (TestClassA $phonySelf) {
                    return func_get_args();
                },
                new TestClassA(),
                ['a', 'b'],
                [new TestClassA(), 'a', 'b'],
            ],
            'Subclass' => [
                function (TestClassA $phonySelf) {
                    return func_get_args();
                },
                new TestClassB(),
                ['a', 'b'],
                [new TestClassB(), 'a', 'b'],
            ],
            'No hint' => [
                function ($phonySelf) {
                    return func_get_args();
                },
                new TestClassA(),
                ['a', 'b'],
                [new TestClassA(), 'a', 'b'],
            ],
            'Wrong name' => [
                function (TestClassA $a) {
                    return func_get_args();
                },
                new TestClassA(),
                [new TestClassA(), 'a', 'b'],
                [new TestClassA(), 'a', 'b'],
            ],
            'No parameters' => [
                function () {
                    return func_get_args();
                },
                new TestClassA(),
                ['a', 'b'],
                ['a', 'b'],
            ],
            'Not a callable object' => [
                'implode',
                new TestClassA(),
                [['a', 'b']],
                'ab',
            ],
        ];
    }

    /**
     * @dataProvider forwardsSelfParameterAutoDetectionData
     */
    public function testForwardsSelfParameterAutoDetection($callback, $self, $arguments, $expected)
    {
        $subject = new StubData(
            $callback,
            $self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );
        $subject->forwards();

        $this->assertEquals($expected, call_user_func_array($subject, $arguments));
    }

    public function testForwardsWithReferenceParameters()
    {
        $this->subject = new StubData(
            $this->referenceCallback,
            $this->self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->forwards([&$a, &$b]);
        $this->subject->invokeWith([&$c, &$d]);

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
        $this->assertSame('', (string) call_user_func($this->subject));
    }

    public function returnsWithReturnTypeData()
    {
        return [
            'bool'   => ['bool',   false],
            'int'    => ['int',    0],
            'float'  => ['float',  .0],
            'string' => ['string', ''],
            'array'  => ['array',  []],
        ];
    }

    /**
     * @dataProvider returnsWithReturnTypeData
     */
    public function testReturnsWithReturnTypes($type, $expected)
    {
        $this->subject = new StubData(
            eval("return function (): $type {};"),
            null,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );
        $this->subject->returns();

        $this->assertSame($expected, call_user_func($this->subject));
    }

    public function testReturnsWithInstanceHandles()
    {
        $handle = Phony::mock();
        $this->subject->returns($handle);

        $this->assertSame($handle->get(), call_user_func($this->subject));
    }

    public function testReturnsArgument()
    {
        $this->assertSame($this->subject, $this->subject->returnsArgument());
        $this->assertSame('a', call_user_func($this->subject, 'a'));
        $this->assertSame('b', call_user_func($this->subject, 'b'));

        $this->assertSame($this->subject, $this->subject->returnsArgument(1));
        $this->assertSame('b', call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertSame('c', call_user_func($this->subject, 'b', 'c', 'd'));

        $this->assertSame($this->subject, $this->subject->returnsArgument(-1));
        $this->assertSame('c', call_user_func($this->subject, 'a', 'b', 'c'));
        $this->assertSame('d', call_user_func($this->subject, 'b', 'c', 'd'));
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

        $thrownExceptions = [];
        for ($i = 0; $i < 2; ++$i) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertEquals([new Exception(), new Exception()], $thrownExceptions);
    }

    public function testThrowsWithInstanceHandles()
    {
        $adaptable = Phony::mock(RuntimeException::class);
        $this->subject->throws($adaptable);

        $this->expectException(RuntimeException::class);
        call_user_func($this->subject);
    }

    public function testThrowsWithException()
    {
        $exceptionA = new Exception();
        $exceptionB = new Exception();
        $this->assertSame($this->subject, $this->subject->throws($exceptionA, $exceptionB));

        $thrownExceptions = [];
        for ($i = 0; $i < 3; ++$i) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertSame([$exceptionA, $exceptionB, $exceptionB], $thrownExceptions);
    }

    public function testThrowsWithMessage()
    {
        $this->assertSame($this->subject, $this->subject->throws('a', 'b'));

        $thrownExceptions = [];
        for ($i = 0; $i < 3; ++$i) {
            try {
                call_user_func($this->subject);
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }

        $this->assertEquals([new Exception('a'), new Exception('b'), new Exception('b')], $thrownExceptions);
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
        $thrownExceptions = [];
        for ($i = 0; $i < 2; ++$i) {
            try {
                call_user_func($this->subject, 'b');
            } catch (Exception $thrownException) {
                $thrownExceptions[] = $thrownException;
            }
        }
        $this->assertEquals([new Exception(), new Exception()], $thrownExceptions);

        $this->assertSame('a', call_user_func($this->subject));
        $this->assertSame('d', call_user_func($this->subject, 'a'));
        $thrownExceptions = [];
        try {
            call_user_func($this->subject, 'b');
        } catch (Exception $thrownException) {
            $thrownExceptions[] = $thrownException;
        }
        $this->assertEquals([new Exception()], $thrownExceptions);

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

        $this->expectException(UnusedStubCriteriaException::class);
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
                ->with(['a', 'b'])->calls($callbackA)
                ->with(['c', 'd'])->calls($callbackA, $callbackB)
        );
        $this->assertSame('default answer', call_user_func($this->subject, ['a', 'b']));
        $this->assertSame(1, $callCountA);
        $this->assertSame(0, $callCountB);
        $this->assertSame('default answer', call_user_func($this->subject, ['c', 'd']));
        $this->assertSame(2, $callCountA);
        $this->assertSame(1, $callCountB);
        $this->assertSame('default answer', call_user_func($this->subject, ['e', 'f']));
        $this->assertSame(2, $callCountA);
        $this->assertSame(1, $callCountB);
    }

    public function testInvokeMethods()
    {
        $this->subject->returns();

        $this->assertSame('', (string) $this->subject->invokeWith());
        $this->assertSame('', (string) $this->subject->invoke());
        $this->assertSame('', (string) call_user_func($this->subject));
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->doesWith($this->referenceCallback, [&$a, &$b]);
        $this->subject->invokeWith([&$c, &$d]);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
    }

    public function testInvokeWithNoRules()
    {
        $stub = new StubData(
            null,
            null,
            null,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );

        $this->assertSame('default answer', $stub());
    }
}
