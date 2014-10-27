<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Reflection;

use Eloquent\Phony\Feature\FeatureDetector;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class FunctionSignatureInspectorTest extends PHPUnit_Framework_TestCase
{
    const CONSTANT_A = 'a';

    protected function setUp()
    {
        $this->featureDetector = new FeatureDetector();
        $this->subject = new FunctionSignatureInspector($this->featureDetector);
    }

    public function testConstructor()
    {
        $this->assertSame($this->featureDetector, $this->subject->featureDetector());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new FunctionSignatureInspector();

        $this->assertSame(FeatureDetector::instance(), $this->subject->featureDetector());
    }

    public function testSignature()
    {
        $function = new ReflectionFunction(
            function (
                $a,
                &$b,
                array $c,
                array &$d,
                \Type $e,
                \Type &$f,
                \Namespaced\Type $g,
                \Namespaced\Type &$h,
                FeatureDetector $i,
                $j = 'string',
                &$k = 111,
                array $l = array('a', 'b', 'c' => 'd'),
                array &$m = null,
                \Type $n = null,
                \Type &$o = null,
                \Namespaced\Type $p = null,
                \Namespaced\Type &$q = null
            ) {}
        );
        $actual = $this->subject->signature($function);
        $expected = array(
            'a' => array('',                                         'mixed                                   ', '', ''),
            'b' => array('',                                         'mixed                                   ', '&', ''),
            'c' => array('array ',                                   'array                                   ', '', ''),
            'd' => array('array ',                                   'array                                   ', '&', ''),
            'e' => array('\Type ',                                   '\Type                                   ', '', ''),
            'f' => array('\Type ',                                   '\Type                                   ', '&', ''),
            'g' => array('\Namespaced\Type ',                        '\Namespaced\Type                        ', '', ''),
            'h' => array('\Namespaced\Type ',                        '\Namespaced\Type                        ', '&', ''),
            'i' => array('\Eloquent\Phony\Feature\FeatureDetector ', '\Eloquent\Phony\Feature\FeatureDetector ', '', ''),
            'j' => array('',                                         'mixed                                   ', '', " = 'string'"),
            'k' => array('',                                         'mixed                                   ', '&', ' = 111'),
            'l' => array('array ',                                   'array                                   ', '', " = array(0 => 'a', 1 => 'b', 'c' => 'd')"),
            'm' => array('array ',                                   'array|null                              ', '&', ' = null'),
            'n' => array('\Type ',                                   '\Type|null                              ', '', ' = null'),
            'o' => array('\Type ',                                   '\Type|null                              ', '&', ' = null'),
            'p' => array('\Namespaced\Type ',                        '\Namespaced\Type|null                   ', '', ' = null'),
            'q' => array('\Namespaced\Type ',                        '\Namespaced\Type|null                   ', '&', ' = null'),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithUnavailableDefaultValue()
    {
        $function = new ReflectionMethod('ReflectionClass', 'getMethods');
        $actual = $this->subject->signature($function);
        $expected = array(
            'filter' => array('', 'mixed ', '', ' = null'),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithDefaultValueConstant()
    {
        if (!$this->featureDetector->isSupported('parameter.default.constant')) {
            $this->markTestSkipped('Requires parameter constant name support in ReflectionParameter.');
        }
        $function = new ReflectionFunction(
            function (
                $a = ReflectionMethod::IS_FINAL,
                $b = self::CONSTANT_A
            ) {}
        );
        $actual = $this->subject->signature($function);
        $expected = array(
            'a' => array('', 'mixed ', '', ' = \ReflectionMethod::IS_FINAL'),
            'b' => array('', 'mixed ', '', ' = \Eloquent\Phony\Reflection\FunctionSignatureInspectorTest::CONSTANT_A'),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithNoDefaultValueConstant()
    {
        if ($this->featureDetector->isSupported('parameter.default.constant')) {
            $this->markTestSkipped('Requires no parameter constant name support in ReflectionParameter.');
        }
        $function = new ReflectionMethod(__CLASS__, 'methodA');
        $actual = $this->subject->signature($function);
        $expected = array(
            'a' => array('', 'mixed ', '', ' = ' . ReflectionMethod::IS_FINAL),
            'b' => array('', 'mixed ', '', " = 'a'"),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testSignatureWithCallableTypeHint()
    {
        if (!$this->featureDetector->isSupported('parameter.type.callable')) {
            $this->markTestSkipped('Requires callable type hint support.');
        }
        $function = new ReflectionFunction(
            eval('return function (callable $a, callable $b = null) {};')
        );
        $actual = $this->subject->signature($function);
        $expected = array(
            'a' => array('callable ', 'callable      ', '', ''),
            'b' => array('callable ', 'callable|null ', '', ' = null'),
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    protected function methodA(
        $a = ReflectionMethod::IS_FINAL,
        $b = self::CONSTANT_A
    ) {}

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
}
