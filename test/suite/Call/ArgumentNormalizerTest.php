<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use PHPUnit\Framework\TestCase;

class ArgumentNormalizerTest extends TestCase
{
    private ArgumentNormalizer $subject;

    protected function setUp(): void
    {
        $this->subject = new ArgumentNormalizer();
    }

    public function testNormalizeEmptyArguments()
    {
        $this->assertSame(
            [],
            $this->subject->normalize([], [])
        );
        $this->assertSame(
            [],
            $this->subject->normalize(['a'], [])
        );
        $this->assertSame(
            [],
            $this->subject->normalize(['a', 'b'], [])
        );
    }

    public function testNormalizePositionalArgumentsWithParameterNames()
    {
        $this->assertSame(
            ['b' => 1],
            $this->subject->normalize(['b', 'a'], [1]),
            'missing arguments'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2],
            $this->subject->normalize(['b', 'a'], [1, 2]),
            'arguments match parameter names'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2, 2 => 333, 3 => 444],
            $this->subject->normalize(['b', 'a'], [1, 2, 333, 444]),
            'extra arguments'
        );
    }

    public function testNormalizePositionalArgumentsWithNoParameterNames()
    {
        $this->assertSame(
            [222, 111],
            $this->subject->normalize([], [222, 111]),
            'in order'
        );
        $this->assertSame(
            [222, 111],
            $this->subject->normalize([], [1 => 111, 0 => 222]),
            'out of order'
        );
    }

    public function testNormalizeNamedArgumentsWithParameterNames()
    {
        $this->assertSame(
            ['b' => 1],
            $this->subject->normalize(['b', 'a'], ['b' => 1]),
            'missing arguments, in order'
        );
        $this->assertSame(
            ['a' => 1],
            $this->subject->normalize(['b', 'a'], ['a' => 1]),
            'missing arguments, out of order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2],
            $this->subject->normalize(['b', 'a'], ['b' => 1, 'a' => 2]),
            'arguments match parameter names, in order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2],
            $this->subject->normalize(['b', 'a'], ['a' => 2, 'b' => 1]),
            'arguments match parameter names, out of order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2, 'c' => 333],
            $this->subject->normalize(['b', 'a'], ['b' => 1, 'a' => 2, 'c' => 333]),
            'extra arguments, in order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2, 'c' => 333, 'd' => 444],
            $this->subject->normalize(['b', 'a'], ['c' => 333, 'a' => 2, 'd' => 444, 'b' => 1]),
            'extra arguments, out of order'
        );
        $this->assertSame(
            ['c' => 444, 'd' => 333],
            $this->subject->normalize(['b', 'a'], ['d' => 333, 'c' => 444]),
            'extra arguments, no arguments match parameter names'
        );
    }

    public function testNormalizeNamedArgumentsWithNoParameterNames()
    {
        $this->assertSame(
            ['a' => 222, 'b' => 111],
            $this->subject->normalize([], ['a' => 222, 'b' => 111]),
            'in order'
        );
        $this->assertSame(
            ['a' => 222, 'b' => 111],
            $this->subject->normalize([], ['b' => 111, 'a' => 222]),
            'out of order'
        );
    }

    public function testNormalizeMixedArgumentsWithParameterNames()
    {
        $this->assertSame(
            ['b' => 1, 'a' => 2],
            $this->subject->normalize(['b', 'a', 'c'], [1, 'a' => 2]),
            'missing arguments, in order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2],
            $this->subject->normalize(['b', 'a', 'c'], ['a' => 2, 0 => 1]),
            'missing arguments, in order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2],
            $this->subject->normalize(['b', 'a'], [1, 'a' => 2]),
            'arguments match parameter names, in order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2],
            $this->subject->normalize(['b', 'a'], ['a' => 2, 0 => 1]),
            'arguments match parameter names, out of order'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2, 'c' => 333],
            $this->subject->normalize(['b', 'a'], [1, 'a' => 2, 'c' => 333]),
            'extra arguments, in order, named extras'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2, 2 => 333, 'c' => 444],
            $this->subject->normalize(['b', 'a'], [1, 2, 333, 'c' => 444]),
            'extra arguments, in order, mixed extras'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2, 'c' => 333, 'd' => 444],
            $this->subject->normalize(['b', 'a'], ['c' => 333, 'a' => 2, 'd' => 444, 0 => 1]),
            'extra arguments, out of order, named extras'
        );
        $this->assertSame(
            ['b' => 1, 'a' => 2, 2 => 333, 'c' => 444],
            $this->subject->normalize(['b', 'a'], ['c' => 444, 1 => 2, 2 => 333, 0 => 1]),
            'extra arguments, out of order, mixed extras'
        );
    }

    public function testNormalizeMixedArgumentsWithNoParameterNames()
    {
        $this->assertSame(
            [444, 333, 'a' => 222, 'b' => 111],
            $this->subject->normalize([], [444, 333, 'a' => 222, 'b' => 111]),
            'in order'
        );
        $this->assertSame(
            [444, 333, 'a' => 222, 'b' => 111],
            $this->subject->normalize([], ['b' => 111, 0 => 444, 'a' => 222, 1 => 333]),
            'out of order'
        );
    }

    public function testNormalizeMaintainsReferences()
    {
        $a = 222;
        $b = 111;
        $c = 444;
        $d = 333;
        $actual = $this->subject->normalize([], [&$c, &$d, 'a' => &$a, 'b' => &$b]);

        $this->assertSame([&$c, &$d, 'a' => &$a, 'b' => &$b], $actual);

        $a = 555;
        $b = 666;
        $c = 777;
        $d = 888;

        $this->assertSame(777, $actual[0]);
        $this->assertSame(888, $actual[1]);
        $this->assertSame(555, $actual['a']);
        $this->assertSame(666, $actual['b']);
    }
}
