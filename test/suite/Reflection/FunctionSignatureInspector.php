<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class FunctionSignatureInspectorTest extends TestCase
{
    const CONSTANT_A = 'a';

    protected function setUp(): void
    {
        $this->featureDetector = new FeatureDetector();
        $this->subject = new FunctionSignatureInspector($this->featureDetector);
    }

    public function testSignature()
    {
        $function = new ReflectionFunction(
            function (
                $a,
                &$b,
                array $c = null,
                array &$d,
                \Type $e = null,
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
            ) {}
        );
        $actual = $this->subject->signature($function);
        $expected = [
            'a' => ['',                                         '',  '', ''],
            'b' => ['',                                         '&', '', ''],
            'c' => ['array ',                                   '',  '', ' = null'],
            'd' => ['array ',                                   '&', '', ''],
            'e' => ['\Type ',                                   '',  '', ' = null'],
            'f' => ['\Type ',                                   '&', '', ''],
            'g' => ['\Namespaced\Type ',                        '',  '', ''],
            'h' => ['\Namespaced\Type ',                        '&', '', ''],
            'i' => ['\Eloquent\Phony\Reflection\FeatureDetector ', '',  '', ''],
            'j' => ['',                                         '',  '', " = 'string'"],
            'k' => ['',                                         '&', '', ' = 111'],
            'm' => ['array ',                                   '&', '', ' = null'],
            'n' => ['\Type ',                                   '',  '', ' = null'],
            'o' => ['\Type ',                                   '&', '', ' = null'],
            'p' => ['\Namespaced\Type ',                        '',  '', ' = null'],
            'q' => ['\Namespaced\Type ',                        '&', '', ' = null'],
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithEmptyParameterList()
    {
        $function = new ReflectionFunction(function () {});
        $actual = $this->subject->signature($function);
        $expected = [];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithArrayDefault()
    {
        $function = new ReflectionFunction(function ($a = ['a', 'b', 'c' => 'd']) {});
        $actual = $this->subject->signature($function);

        $this->assertArrayHasKey('a', $actual);
        $this->assertSame(['a', 'b', 'c' => 'd'], eval('return $r' . $actual['a'][3] . ';'));
    }

    public function testSignatureWithUnavailableDefaultValue()
    {
        $function = new ReflectionMethod('ReflectionClass', 'getMethods');
        $actual = $this->subject->signature($function);
        $expected = [
            'filter' => ['', '', '', ' = null'],
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithCallableTypeHint()
    {
        $function = new ReflectionFunction(
            eval('return function (callable $a = null, callable $b, callable $c = null) {};')
        );
        $actual = $this->subject->signature($function);
        $expected = [
            'a' => ['callable ', '', '', ' = null'],
            'b' => ['callable ', '', '', ''],
            'c' => ['callable ', '', '', ' = null'],
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithConstantDefault()
    {
        $function = new ReflectionMethod($this, 'methodA');
        $actual = $this->subject->signature($function);
        $expected = [
            'a' => ['', '', '', ' = 4'],
            'b' => ['', '', '', " = 'a'"],
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithSelfTypeHint()
    {
        $function = new ReflectionMethod($this, 'methodB');
        $actual = $this->subject->signature($function);
        $expected = [
            'a' => ['\Eloquent\Phony\Reflection\FunctionSignatureInspectorTest ', '', '', ' = null'],
            'b' => ['\Eloquent\Phony\Reflection\FunctionSignatureInspectorTest ', '', '', ''],
        ];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithVariadicParameter()
    {
        $function = new ReflectionFunction(eval('return function(...$a){};'));
        $actual = $this->subject->signature($function);
        $expected = ['a' => ['', '', '...', '']];

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }

    protected function methodA($a = ReflectionMethod::IS_FINAL, $b = self::CONSTANT_A)
    {
    }

    protected function methodB(self $a = null, self $b)
    {
    }
}
