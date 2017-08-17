<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Reflection;

use Eloquent\Phony\Invocation\InvocableInspector;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class HhvmFunctionSignatureInspectorTest extends TestCase
{
    const CONSTANT_A = 'a';

    protected function setUp()
    {
        $this->featureDetector = new FeatureDetector();

        if (!$this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('Requires HHVM.');
        }

        $this->invocableInspector = new InvocableInspector();
        $this->subject = new HhvmFunctionSignatureInspector($this->invocableInspector, $this->featureDetector);
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
        $expected = array(
            'a' => array('',                                         '',  '', ''),
            'b' => array('',                                         '&', '', ''),
            'c' => array('array ',                                   '',  '', ' = null'),
            'd' => array('array ',                                   '&', '', ''),
            'e' => array('\Type ',                                   '',  '', ' = null'),
            'f' => array('\Type ',                                   '&', '', ''),
            'g' => array('\Namespaced\Type ',                        '',  '', ''),
            'h' => array('\Namespaced\Type ',                        '&', '', ''),
            'i' => array('\Eloquent\Phony\Reflection\FeatureDetector ', '',  '', ''),
            'j' => array('',                                         '',  '', " = 'string'"),
            'k' => array('',                                         '&', '', ' = 111'),
            'm' => array('array ',                                   '&', '', ' = null'),
            'n' => array('\Type ',                                   '',  '', ' = null'),
            'o' => array('\Type ',                                   '&', '', ' = null'),
            'p' => array('\Namespaced\Type ',                        '',  '', ' = null'),
            'q' => array('\Namespaced\Type ',                        '&', '', ' = null'),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithEmptyParameterList()
    {
        $function = new ReflectionFunction(function () {});
        $actual = $this->subject->signature($function);
        $expected = array();

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithArrayDefault()
    {
        $function = new ReflectionFunction(function ($a = array('a', 'b', 'c' => 'd')) {});
        $actual = $this->subject->signature($function);

        $this->assertArrayHasKey('a', $actual);
        $this->assertSame(array('a', 'b', 'c' => 'd'), eval('return $r' . $actual['a'][3] . ';'));
    }

    public function testSignatureWithUnavailableDefaultValue()
    {
        $function = new ReflectionMethod('ReflectionClass', 'getMethods');
        $actual = $this->subject->signature($function);
        $expected = array(
            'filter' => array('', '', '', ' = null'),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithCallableTypeHint()
    {
        if (!$this->featureDetector->isSupported('type.callable')) {
            $this->markTestSkipped('Requires callable type hint support.');
        }

        $function = new ReflectionFunction(
            eval('return function (callable $a = null, callable $b, callable $c = null) {};')
        );
        $actual = $this->subject->signature($function);
        $expected = array(
            'a' => array('callable ', '', '', ' = null'),
            'b' => array('callable ', '', '', ''),
            'c' => array('callable ', '', '', ' = null'),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithConstantDefault()
    {
        if (!$this->featureDetector->isSupported('parameter.default.constant')) {
            $this->markTestSkipped('Requires support for constants as parameter defaults.');
        }

        $function = new ReflectionMethod($this, 'methodA');
        $actual = $this->subject->signature($function);
        $expected = array(
            'a' => array('', '', '', ' = 4'),
            'b' => array('', '', '', " = 'a'"),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithSelfTypeHint()
    {
        $function = new ReflectionMethod($this, 'methodB');
        $actual = $this->subject->signature($function);
        $expected = array(
            'a' => array('\Eloquent\Phony\Reflection\HhvmFunctionSignatureInspectorTest ', '', '', ' = null'),
            'b' => array('\Eloquent\Phony\Reflection\HhvmFunctionSignatureInspectorTest ', '', '', ''),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testCallbackSignature()
    {
        $callback = function ($a, array $b = null) {};
        $expected = array(
            'a' => array('', '', '', ''),
            'b' => array('array ', '', '', ' = null'),
        );
        $actual = $this->subject->callbackSignature($callback);

        $this->assertSame($actual, $expected);
    }

    public function testSignatureWithVariadicParameter()
    {
        if (!$this->featureDetector->isSupported('parameter.variadic')) {
            $this->markTestSkipped('Requires variadic parameters.');
        }

        $function = new ReflectionFunction(eval('return function(...$a){};'));
        $actual = $this->subject->signature($function);
        $expected = array('a' => array('', '', '...', ''));

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    protected function methodA($a = ReflectionMethod::IS_FINAL, $b = self::CONSTANT_A)
    {
    }

    protected function methodB(self $a = null, self $b)
    {
    }

    public function testInstance()
    {
        $class = __NAMESPACE__ . '\FunctionSignatureInspector';
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
