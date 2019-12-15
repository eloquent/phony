<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer\Builder;

use ArrayIterator;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Test\TupleIterator;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GeneratorAnswerBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        $this->self = (object) [];
        $this->stub = StubFactory::instance()->create(null, null)->setSelf($this->self);
        $this->featureDetector = FeatureDetector::instance();
        $this->invocableInspector = new InvocableInspector();
        $this->invoker = new Invoker();
        $this->subject = new GeneratorAnswerBuilder(
            $this->stub,
            $this->invocableInspector,
            $this->invoker
        );

        $this->answer = $this->subject->answer();

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

        $this->referenceCallback = function (&$a, &$b = null, &$c = null, &$d = null) {
            $a = 'a';
            $b = 'b';
            $c = 'c';
            $d = 'd';
        };

        $this->arguments = Arguments::create();
    }

    public function testCalls()
    {
        $this->assertSame(
            $this->subject,
            $this->subject->calls($this->callbackA, $this->callbackB)->calls($this->callbackC)
        );

        iterator_to_array(call_user_func($this->answer, $this->self, Arguments::create('a', 'b')));
        $expected = [['a', 'b']];

        $this->assertSame($expected, $this->callsA);
        $this->assertSame($expected, $this->callsB);
        $this->assertSame($expected, $this->callsC);
    }

    public function testCallsWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $this->subject->calls($this->referenceCallback);
        iterator_to_array(call_user_func($this->answer, $this->self, new Arguments([&$a, &$b])));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testCallsWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsWith($this->callbackA, ['A', 'B'], true, true, true)
                ->callsWith($this->callbackA, ['C', 'D'], true, true, true)
                ->callsWith($this->callbackB, ['E', 'F'], true, true, true)
        );

        $arguments = Arguments::create('a', 'b');
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

        $this->assertEquals(
            [
                [$this->self, 'A', 'B', $arguments, 'a', 'b'],
                [$this->self, 'C', 'D', $arguments, 'a', 'b'],
            ],
            $this->callsA
        );
        $this->assertEquals(
            [
                [$this->self, 'E', 'F', $arguments, 'a', 'b'],
            ],
            $this->callsB
        );
    }

    public function testCallsWithDefaults()
    {
        $this->assertSame($this->subject, $this->subject->callsWith($this->callbackA));

        $arguments = Arguments::create('a', 'b');
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

        $this->assertEquals([['a', 'b']], $this->callsA);
    }

    public function testCallsWithSelfParameterAutoDetection()
    {
        $actual = null;
        $this->subject->callsWith(
            function ($phonySelf) use (&$actual) {
                    $actual = func_get_args();
                }
        )
            ->returns();
        $arguments = Arguments::create('a', 'b');
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

        $this->assertSame([$this->self, 'a', 'b'], $actual);
    }

    public function testCallsWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->callsWith($this->referenceCallback, [&$a, &$b]);
        $arguments = new Arguments([&$c, &$d]);
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

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
                ->callsArgument(0, 1)
                ->callsArgument()
        );

        $arguments = Arguments::create($this->callbackA, $this->callbackB);
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

        $this->assertEquals(
            [
                [$this->callbackA, $this->callbackB],
                [$this->callbackA, $this->callbackB],
            ],
            $this->callsA
        );
        $this->assertEquals(
            [
                [$this->callbackA, $this->callbackB],
            ],
            $this->callsB
        );
    }

    public function testCallsArgumentWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->callsArgumentWith()
                ->callsArgumentWith(0, ['A', 'B'], true, true, true)
                ->callsArgumentWith(0, ['C', 'D'], true, true, true)
                ->callsArgumentWith(1, ['E', 'F'], true, true, true)
        );

        $arguments = Arguments::create($this->callbackA, $this->callbackB);
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

        $this->assertEquals(
            [
                [$this->callbackA, $this->callbackB],
                [$this->self, 'A', 'B', $arguments, $this->callbackA, $this->callbackB],
                [$this->self, 'C', 'D', $arguments, $this->callbackA, $this->callbackB],
            ],
            $this->callsA
        );
        $this->assertEquals(
            [
                [$this->self, 'E', 'F', $arguments, $this->callbackA, $this->callbackB],
            ],
            $this->callsB
        );
    }

    public function testCallsArgumentWithWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $this->subject->callsArgumentWith(2, [&$a, &$b], false, false, true);
        $arguments = new Arguments([&$c, &$d, $this->referenceCallback]);
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

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
        );

        $a = null;
        $b = null;
        $c = null;
        $arguments = new Arguments([&$a, &$b, &$c]);
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
    }

    public function testSetsArgumentWithInstanceHandles()
    {
        $handle = Phony::mock();
        $this->subject->setsArgument(0, $handle);

        $a = null;
        $arguments = new Arguments([&$a]);
        iterator_to_array(call_user_func($this->answer, $this->self, $arguments));

        $this->assertSame($handle->get(), $a);
    }

    public function testYields()
    {
        $this->assertSame($this->subject, $this->subject->yields('a', 'b')->yields('c')->yields());
        $this->assertSame(
            ['a' => 'b', 0 => 'c', 1 => null],
            iterator_to_array(call_user_func($this->answer, $this->self, $this->arguments))
        );
    }

    public function testYieldsWithSubRequests()
    {
        $arguments = Arguments::create('b', 'c');

        $this->assertSame($this->subject, $this->subject->calls($this->callbackA, $this->callbackB)->yields('a'));
        $this->assertSame(['a'], iterator_to_array(call_user_func($this->answer, $this->self, $arguments)));
        $this->assertSame([['b', 'c']], $this->callsA);
        $this->assertSame([['b', 'c']], $this->callsB);
    }

    public function testYieldsWithInstanceHandles()
    {
        $handle = Phony::mock();
        $this->subject->yields($handle);

        $this->assertSame(
            [$handle->get()],
            iterator_to_array(call_user_func($this->answer, $this->self, $this->arguments))
        );
    }

    public function testYieldsWithInstanceHandleKeys()
    {
        $handle = Phony::mock();
        $this->subject->yields($handle, 'a');
        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $this->assertSame($handle->get(), $generator->key());
    }

    public function testYieldsFrom()
    {
        $values = ['a' => 'b', 'c' => 'd'];

        $this->assertSame($this->subject, $this->subject->yieldsFrom($values));
        $this->assertSame($values, iterator_to_array(call_user_func($this->answer, $this->self, $this->arguments)));
    }

    public function testYieldsFromWithIterator()
    {
        $values = ['a' => 'b', 'c' => 'd'];

        $this->assertSame($this->subject, $this->subject->yieldsFrom(new ArrayIterator($values)));
        $this->assertSame($values, iterator_to_array(call_user_func($this->answer, $this->self, $this->arguments)));
    }

    public function testYieldsFromWithSubRequests()
    {
        $arguments = Arguments::create('b', 'c');

        $this->assertSame($this->subject, $this->subject->calls($this->callbackA, $this->callbackB)->yieldsFrom(['a']));
        $this->assertSame(['a'], iterator_to_array(call_user_func($this->answer, $this->self, $arguments)));
        $this->assertSame([['b', 'c']], $this->callsA);
        $this->assertSame([['b', 'c']], $this->callsB);
    }

    public function testYieldsFromWithInstanceHandles()
    {
        $handle = Phony::mock();
        $this->subject->yieldsFrom([$handle]);

        $this->assertSame(
            [$handle->get()],
            iterator_to_array(call_user_func($this->answer, $this->self, $this->arguments))
        );
    }

    public function testYieldsFromWithInstanceHandleKeys()
    {
        $handle = Phony::mock();
        $this->subject->yieldsFrom(
            new TupleIterator(
                [
                    [$handle, 'a'],
                ]
            )
        );
        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $this->assertSame($handle->get(), $generator->key());
    }

    public function testReturns()
    {
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returns());
        $this->assertSame(
            ['a', 'b'],
            iterator_to_array(call_user_func($this->answer, $this->self, $this->arguments))
        );
    }

    public function testReturnsWithExplicitNull()
    {
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returns(null));
    }

    public function testReturnsWithValue()
    {
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returns('c'));

        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame('c', $generator->getReturn());
    }

    public function testReturnsWithInstanceHandleValue()
    {
        $handle = Phony::mock();
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returns($handle));

        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame($handle->get(), $generator->getReturn());
    }

    public function testReturnsWithMulitpleValues()
    {
        $this->stub->doesWith($this->answer, [], true, true, false);

        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returns('c', 'd'));

        $generator = call_user_func($this->stub);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame('c', $generator->getReturn());

        $generator = call_user_func($this->stub);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame('d', $generator->getReturn());
    }

    public function testReturnsArgument()
    {
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returnsArgument(1));

        $arguments = Arguments::create('c', 'd');
        $generator = call_user_func($this->answer, $this->self, $arguments);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame('d', $generator->getReturn());
    }

    public function testReturnsArgumentWithoutIndex()
    {
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returnsArgument());

        $arguments = Arguments::create('c', 'd');
        $generator = call_user_func($this->answer, $this->self, $arguments);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame('c', $generator->getReturn());
    }

    public function testReturnsArgumentWithNegativeIndex()
    {
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returnsArgument(-1));

        $arguments = Arguments::create('c', 'd', 'e');
        $generator = call_user_func($this->answer, $this->self, $arguments);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame('e', $generator->getReturn());
    }

    public function testReturnsArgumentWithUndefinedArgument()
    {
        $this->assertSame($this->stub, $this->subject->returnsArgument(1));

        $arguments = Arguments::create();
        $generator = call_user_func($this->answer, $this->self, $arguments);

        $this->expectException(UndefinedArgumentException::class);
        iterator_to_array($generator);
    }

    public function testReturnsSelf()
    {
        $this->assertSame($this->stub, $this->subject->yields('a')->yields('b')->returnsSelf());

        $arguments = Arguments::create('c', 'd');
        $generator = call_user_func($this->answer, $this->self, $arguments);

        $this->assertSame(['a', 'b'], iterator_to_array($generator));
        $this->assertSame($this->self, $generator->getReturn());
    }

    public function testThrows()
    {
        $this->subject->yields('a')->yields('b');

        $this->assertSame($this->stub, $this->subject->throws());
        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $values = [];
        $actual = null;

        try {
            foreach ($generator as $key => $value) {
                $values[$key] = $value;
            }
        } catch (Exception $actual) {
        }

        $this->assertInstanceOf(Exception::class, $actual);
        $this->assertSame(['a', 'b'], $values);
    }

    public function testThrowsWithInstanceHandles()
    {
        $handle = Phony::mock(RuntimeException::class);
        $this->subject->throws($handle);
        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $actual = null;

        try {
            iterator_to_array($generator);
        } catch (Exception $actual) {
        }

        $this->assertSame($handle->get(), $actual);
    }

    public function testThrowsWithException()
    {
        $exception = new Exception();
        $this->subject->throws($exception);
        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $actual = null;

        try {
            iterator_to_array($generator);
        } catch (Exception $actual) {
        }

        $this->assertSame($exception, $actual);
    }

    public function testThrowsWithMultipleExceptions()
    {
        $this->stub->doesWith($this->answer, [], true, true, false);
        $exceptionA = new Exception('a');
        $exceptionB = new Exception('b');
        $this->subject->throws($exceptionA, $exceptionB);
        $generator = call_user_func($this->stub);
        $actual = null;

        try {
            iterator_to_array($generator);
        } catch (Exception $actual) {
        }

        $this->assertSame($exceptionA, $actual);

        $generator = call_user_func($this->stub);
        $actual = null;

        try {
            iterator_to_array($generator);
        } catch (Exception $actual) {
        }

        $this->assertSame($exceptionB, $actual);

        $generator = call_user_func($this->stub);
        $actual = null;

        try {
            iterator_to_array($generator);
        } catch (Exception $actual) {
        }

        $this->assertSame($exceptionB, $actual);
    }

    public function testThrowsWithMessage()
    {
        $this->subject->throws('a');
        $generator = call_user_func($this->answer, $this->self, $this->arguments);

        $actual = null;

        try {
            iterator_to_array($generator);
        } catch (Exception $actual) {
        }

        $this->assertEquals(new Exception('a'), $actual);
    }
}
