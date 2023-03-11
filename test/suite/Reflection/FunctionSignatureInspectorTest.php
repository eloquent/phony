<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use AllowDynamicProperties;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestInvocable;
use Eloquent\Phony\Test\TestTraitWithSelfType;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;

#[AllowDynamicProperties]
class FunctionSignatureInspectorTest extends TestCase
{
    const CONSTANT_A = 'a';

    protected function setUp(): void
    {
        $this->subject = new FunctionSignatureInspector();
    }

    public function testSignature()
    {
        $function = new ReflectionFunction(
            function (
                $a,
                &$b,
                ?array $c,
                array &$d,
                ?\Type $e,
                \Type &$f,
                \Namespaced\Type $g,
                \Namespaced\Type &$h,
                FeatureDetector $i,
                $j = 'string',
                &$k = 111,
                array &$m = null,
                \Type $n = null,
                \Type &$o = null,
                \Namespaced\Type $p = null,
                \Namespaced\Type &$q = null
            ): void {}
        );
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => ['',                                         '',  '', ''],
                'b' => ['',                                         '&', '', ''],
                'c' => ['?array ',                                   '',  '', ''],
                'd' => ['array ',                                   '&', '', ''],
                'e' => ['?\Type ',                                   '',  '', ''],
                'f' => ['\Type ',                                   '&', '', ''],
                'g' => ['\Namespaced\Type ',                        '',  '', ''],
                'h' => ['\Namespaced\Type ',                        '&', '', ''],
                'i' => ['\Eloquent\Phony\Reflection\FeatureDetector ', '',  '', ''],
                'j' => ['',                                         '',  '', " = 'string'"],
                'k' => ['',                                         '&', '', ' = 111'],
                'm' => ['?array ',                                   '&', '', ' = null'],
                'n' => ['?\Type ',                                   '',  '', ' = null'],
                'o' => ['?\Type ',                                   '&', '', ' = null'],
                'p' => ['?\Namespaced\Type ',                        '',  '', ' = null'],
                'q' => ['?\Namespaced\Type ',                        '&', '', ' = null'],
            ],
            'void',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithEmptyParameterList()
    {
        $function = new ReflectionFunction(function () {});
        $actual = $this->subject->signature($function);
        $expected = [[], ''];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithArrayDefault()
    {
        $function = new ReflectionFunction(function ($a = ['a', 'b', 'c' => 'd']) {});
        $actual = $this->subject->signature($function);

        $this->assertArrayHasKey('a', $actual[0]);
        $this->assertSame(['a', 'b', 'c' => 'd'], eval('return $r' . $actual[0]['a'][3] . ';'));
    }

    /**
     * @requires PHP < 8.1
     */
    public function testSignatureWithUnavailableDefaultValuePhp80()
    {
        $function = new ReflectionMethod('ReflectionClass', 'getMethods');
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'filter' => ['?int ', '', '', ' = null'],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testSignatureWithUnavailableDefaultValuePhp81()
    {
        $function = new ReflectionMethod('ReflectionClass', 'getMethods');
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'filter' => ['?int ', '', '', ' = null'],
            ],
            'array',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithCallableTypeHint()
    {
        $function = new ReflectionFunction(function (callable $a = null, callable $b, callable $c = null) {});
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => ['?callable ', '', '', ''],
                'b' => ['callable ', '', '', ''],
                'c' => ['?callable ', '', '', ' = null'],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithConstantDefault()
    {
        $function = new ReflectionMethod($this, 'methodA');
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => ['', '', '', sprintf(' = %d', ReflectionMethod::IS_FINAL)],
                'b' => ['', '', '', " = 'a'"],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithSelfTypeHint()
    {
        $function = new ReflectionMethod($this, 'methodB');
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => [sprintf('?\%s ', FunctionSignatureInspectorTest::class), '', '', ''],
                'b' => [sprintf('\%s ', FunctionSignatureInspectorTest::class), '', '', ''],
            ],
            sprintf('\%s', FunctionSignatureInspectorTest::class),
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithTraitSelfTypeHint()
    {
        $function = new ReflectionMethod(TestTraitWithSelfType::class, 'method');
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => ['self ', '', '', ''],
            ],
            'self',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithParentTypeHint()
    {
        $function = new ReflectionMethod($this, 'methodC');
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => [sprintf('?\%s ', TestCase::class), '', '', ''],
                'b' => [sprintf('\%s ', TestCase::class), '', '', ''],
            ],
            sprintf('\%s', TestCase::class),
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithStaticTypeHint()
    {
        eval('class TestClassWithStaticReturnType { public function methodA(): static {}}');
        $function = new ReflectionMethod('TestClassWithStaticReturnType', 'methodA');
        $actual = $this->subject->signature($function);
        $expected = [[], 'static'];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithVariadicParameter()
    {
        $function = new ReflectionFunction(function (...$a) {});
        $actual = $this->subject->signature($function);
        $expected = [['a' => ['', '', '...', '']], ''];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function signatureWithReturnTypeData()
    {
        return [
            ['',      function () {}],
            ['array', function (): array { return []; }],
            ['bool',  function (): bool { return false; }],
            ['callable',  function (): callable { return function () {}; }],
            ['float',  function (): float { return .0; }],
            ['int',  function (): int { return 0; }],
            ['object',  function (): object { return (object) []; }],
            ['string',  function (): string { return ''; }],
            ['void',  function (): void {}],
        ];
    }

    /**
     * @dataProvider signatureWithReturnTypeData
     */
    public function testSignatureWithReturnType(string $expected, callable $function)
    {
        list(, $actual) = $this->subject->signature(new ReflectionFunction($function));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testSignatureWithIterableReturnType()
    {
        list(, $actual) = $this->subject->signature(new ReflectionFunction(function (): iterable {}));

        $this->assertEquals('\Traversable|array', $actual);
    }

    public function testSignatureWithMixedReturnType()
    {
        list(, $actual) = $this->subject->signature(new ReflectionFunction(function (): mixed {}));

        $this->assertEquals('mixed', $actual);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testSignatureWithNeverReturnType()
    {
        list(, $actual) = $this->subject->signature(new ReflectionFunction(function (): never {}));

        $this->assertEquals('never', $actual);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testSignatureWithTrueReturnType()
    {
        $actual = $this->subject->signature(new ReflectionFunction(eval('return function (true $a): true {};')));

        $this->assertEquals([['a' => ['true ', '', '', '']], 'true'], $actual);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testSignatureWithFalseReturnType()
    {
        $actual = $this->subject->signature(new ReflectionFunction(eval('return function (false $a): false {};')));

        $this->assertEquals([['a' => ['false ', '', '', '']], 'false'], $actual);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testSignatureWithNullReturnType()
    {
        $actual = $this->subject->signature(new ReflectionFunction(eval('return function (null $a): null {};')));

        $this->assertEquals([['a' => ['null ', '', '', '']], 'null'], $actual);
    }

    public function testSignatureWithUnionType()
    {
        $actual = 'callable|object|array|string|int|float|false|null';
        $expected = 'callable|object|array|string|int|float|false|null';
        $function = new ReflectionFunction(
            eval(sprintf('return function (%s $a): %s {};', $actual, $actual))
        );
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => [sprintf('%s ', $expected), '', '', ''],
            ],
            $expected,
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testSignatureWithIntersectionType()
    {
        $actual = 'Countable&Iterator';
        $expected = '\Countable&\Iterator';
        $function = new ReflectionFunction(
            eval(sprintf('return function (%s $a): %s {};', $actual, $actual))
        );
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => [sprintf('%s ', $expected), '', '', ''],
            ],
            $expected,
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testSignatureWithDnfType()
    {
        $actual = '(Countable&Iterator)|(Countable&IteratorAggregate)';
        $expected = '(\Countable&\Iterator)|(\Countable&\IteratorAggregate)';
        $function = new ReflectionFunction(
            eval(sprintf('return function (%s $a): %s {};', $actual, $actual))
        );
        $actual = $this->subject->signature($function);
        $expected = [
            [
                'a' => [sprintf('%s ', $expected), '', '', ''],
            ],
            $expected,
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testSignatureWithTentativeReturnType()
    {
        $function = new ReflectionMethod('Exception', '__wakeup');
        $actual = $this->subject->signature($function);
        $expected = [[], 'void'];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureFromCallbackWithString()
    {
        $callback = 'implode';
        $actual = $this->subject->signatureFromCallback($callback);
        $expected = [
            [
                'separator' => ['array|string ', '', '', ''],
                'array' => ['?array ', '', '', ' = null'],
            ],
            'string',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureFromCallbackWithStaticMethodArray()
    {
        $callback = [TestClassA::class, 'testClassAStaticMethodB'];
        $actual = $this->subject->signatureFromCallback($callback);
        $expected = [
            [
                'first' => ['', '', '', ''],
                'second' => ['', '', '', ''],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureFromCallbackWithInstanceMethodArray()
    {
        $obj = new TestClassA();
        $callback = [$obj, 'testClassAMethodB'];
        $actual = $this->subject->signatureFromCallback($callback);
        $expected = [
            [
                'first' => ['', '', '', ''],
                'second' => ['', '', '', ''],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureFromCallbackWithStaticMethodString()
    {
        $callback = TestClassA::class . '::testClassAStaticMethodB';
        $actual = $this->subject->signatureFromCallback($callback);
        $expected = [
            [
                'first' => ['', '', '', ''],
                'second' => ['', '', '', ''],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * @requires PHP < 8.2
     */
    public function testSignatureFromCallbackWithRelativeStaticMethodArray()
    {
        $callback = [TestClassB::class, 'parent::testClassAStaticMethodB'];
        $actual = $this->subject->signatureFromCallback($callback);
        $expected = [
            [
                'first' => ['', '', '', ''],
                'second' => ['', '', '', ''],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureFromCallbackWithInvocable()
    {
        $callback = new TestInvocable();
        $actual = $this->subject->signatureFromCallback($callback);
        $expected = [
            [
                'arguments' => ['', '', '...', ''],
            ],
            '',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureFromCallbackWithClosure()
    {
        $callback = function (string $a, int $b): bool {};
        $actual = $this->subject->signatureFromCallback($callback);
        $expected = [
            [
                'a' => ['string ', '', '', ''],
                'b' => ['int ', '', '', ''],
            ],
            'bool',
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    protected function methodA($a = ReflectionMethod::IS_FINAL, $b = self::CONSTANT_A)
    {
    }

    protected function methodB(self $a = null, self $b): self
    {
        return $this;
    }

    protected function methodC(parent $a = null, parent $b): parent
    {
        return $this;
    }
}
