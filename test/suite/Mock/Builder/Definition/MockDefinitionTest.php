<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Mock\Builder\Definition\Method\CustomMethodDefinition;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionCollection;
use Eloquent\Phony\Mock\Builder\Definition\Method\RealMethodDefinition;
use Eloquent\Phony\Mock\Builder\Definition\Method\TraitMethodDefinition;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class MockDefinitionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->featureDetector = new FeatureDetector();
        $this->isTraitSupported = $this->featureDetector->isSupported('trait');
        $this->isRelaxedKeywordsSupported = $this->featureDetector->isSupported('parser.relaxed-keywords');

        $this->typeNames = array(
            'Countable',
            'Eloquent\Phony\Test\TestClassB',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Eloquent\Phony\Test\TestInterfaceB',
            'Iterator',
        );
        $this->typeNamesTraits = array(
            'Countable',
            'Eloquent\Phony\Test\TestClassB',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Eloquent\Phony\Test\TestInterfaceB',
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
            'Iterator',
        );
        $this->parentClassName = 'Eloquent\Phony\Test\TestClassB';
        $this->interfaceNames = array(
            'Countable',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Eloquent\Phony\Test\TestInterfaceB',
            'Iterator',
        );
        $this->traitNames = array(
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
        );

        $this->callbackA = function () {};
        $this->callbackB = function () {};
        $this->callbackC = function () {};
        $this->callbackD = function () {};
        $this->callbackE = function () {};
        $this->callbackF = function () {};

        $this->callbackReflectorA = new ReflectionFunction($this->callbackA);
        $this->callbackReflectorB = new ReflectionFunction($this->callbackB);
        $this->callbackReflectorC = new ReflectionFunction($this->callbackC);
        $this->callbackReflectorD = new ReflectionFunction($this->callbackD);
        $this->callbackReflectorE = new ReflectionFunction($this->callbackE);
        $this->callbackReflectorF = new ReflectionFunction($this->callbackF);
    }

    protected function setUpWith($typeNames)
    {
        $this->types = array();

        foreach ($typeNames as $typeName) {
            $this->types[$typeName] = new ReflectionClass($typeName);
        }

        $this->customMethods = array(
            'methodA' => array($this->callbackA, $this->callbackReflectorA),
            'methodB' => array($this->callbackB, $this->callbackReflectorB),
            'methodC' => array($this->callbackC, $this->callbackReflectorC),
        );
        $this->customProperties = array('a' => 'b', 'c' => 'd');
        $this->customStaticMethods = array(
            'methodD' => array($this->callbackD, $this->callbackReflectorD),
            'methodE' => array($this->callbackE, $this->callbackReflectorE),
            'methodF' => array($this->callbackF, $this->callbackReflectorF),
        );
        $this->customStaticProperties = array('e' => 'f', 'g' => 'h');
        $this->customConstants = array('i' => 'j', 'k' => 'l');
        $this->className = 'ClassName';
        $this->subject = new MockDefinition(
            $this->types,
            $this->customMethods,
            $this->customProperties,
            $this->customStaticMethods,
            $this->customStaticProperties,
            $this->customConstants,
            $this->className,
            $this->isTraitSupported,
            $this->isRelaxedKeywordsSupported
        );
    }

    public function testConstructor()
    {
        $this->setUpWith($this->typeNames);

        $this->assertSame($this->types, $this->subject->types());
        $this->assertEquals($this->customMethods, $this->subject->customMethods());
        $this->assertSame($this->customProperties, $this->subject->customProperties());
        $this->assertEquals($this->customStaticMethods, $this->subject->customStaticMethods());
        $this->assertSame($this->customStaticProperties, $this->subject->customStaticProperties());
        $this->assertSame($this->customConstants, $this->subject->customConstants());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->typeNames, $this->subject->typeNames());
        $this->assertSame($this->parentClassName, $this->subject->parentClassName());
        $this->assertSame($this->interfaceNames, $this->subject->interfaceNames());
        $this->assertSame(array(), $this->subject->traitNames());
    }

    public function testConstructorWithTraits()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->setUpWith($this->typeNamesTraits);

        $this->assertSame($this->types, $this->subject->types());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->typeNamesTraits, $this->subject->typeNames());
        $this->assertSame($this->parentClassName, $this->subject->parentClassName());
        $this->assertSame($this->interfaceNames, $this->subject->interfaceNames());
        $this->assertSame($this->traitNames, $this->subject->traitNames());
    }

    public function testMethods()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith($this->typeNames);

        $expected = new MethodDefinitionCollection(
            array(
                'count' => new RealMethodDefinition(new ReflectionMethod('Countable::count'), 'count'),
                'current' => new RealMethodDefinition(new ReflectionMethod('Iterator::current'), 'current'),
                'key' => new RealMethodDefinition(new ReflectionMethod('Iterator::key'), 'key'),
                'methodA' => new CustomMethodDefinition(
                    false,
                    'methodA',
                    $this->callbackA,
                    new ReflectionFunction($this->callbackA)
                ),
                'methodB' => new CustomMethodDefinition(
                    false,
                    'methodB',
                    $this->callbackB,
                    new ReflectionFunction($this->callbackB)
                ),
                'methodC' => new CustomMethodDefinition(
                    false,
                    'methodC',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
                'methodD' => new CustomMethodDefinition(
                    true,
                    'methodD',
                    $this->callbackD,
                    new ReflectionFunction($this->callbackD)
                ),
                'methodE' => new CustomMethodDefinition(
                    true,
                    'methodE',
                    $this->callbackE,
                    new ReflectionFunction($this->callbackE)
                ),
                'methodF' => new CustomMethodDefinition(
                    true,
                    'methodF',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
                'next' => new RealMethodDefinition(new ReflectionMethod('Iterator::next'), 'next'),
                'rewind' => new RealMethodDefinition(new ReflectionMethod('Iterator::rewind'), 'rewind'),
                'testClassAMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodA'),
                    'testClassAMethodA'
                ),
                'testClassAMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                'testClassAMethodC' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodC'),
                    'testClassAMethodC'
                ),
                'testClassAMethodD' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodD'),
                    'testClassAMethodD'
                ),
                'testClassAStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
                'testClassAStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodB'),
                    'testClassAStaticMethodB'
                ),
                'testClassAStaticMethodC' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodC'),
                    'testClassAStaticMethodC'
                ),
                'testClassAStaticMethodD' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodD'),
                    'testClassAStaticMethodD'
                ),
                'testClassBMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBMethodA'),
                    'testClassBMethodA'
                ),
                'testClassBMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBMethodB'),
                    'testClassBMethodB'
                ),
                'testClassBStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBStaticMethodA'),
                    'testClassBStaticMethodA'
                ),
                'testClassBStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBStaticMethodB'),
                    'testClassBStaticMethodB'
                ),
                'valid' => new RealMethodDefinition(new ReflectionMethod('Iterator::valid'), 'valid'),
                '__call' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::__call'),
                    '__call'
                ),
                '__callStatic' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::__callStatic'),
                    '__callStatic'
                ),
            ),
            array()
        );
        $actual = $this->subject->methods();

        $this->assertEquals($expected, $actual);
        $this->assertSame($actual, $this->subject->methods());
    }

    public function testMethodsWithTraits()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith($this->typeNamesTraits);

        $expected = new MethodDefinitionCollection(
            array(
                'count' => new RealMethodDefinition(new ReflectionMethod('Countable::count'), 'count'),
                'current' => new RealMethodDefinition(new ReflectionMethod('Iterator::current'), 'current'),
                'key' => new RealMethodDefinition(new ReflectionMethod('Iterator::key'), 'key'),
                'methodA' => new CustomMethodDefinition(
                    false,
                    'methodA',
                    $this->callbackA,
                    new ReflectionFunction($this->callbackA)
                ),
                'methodB' => new CustomMethodDefinition(
                    false,
                    'methodB',
                    $this->callbackB,
                    new ReflectionFunction($this->callbackB)
                ),
                'methodC' => new CustomMethodDefinition(
                    false,
                    'methodC',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
                'methodD' => new CustomMethodDefinition(
                    true,
                    'methodD',
                    $this->callbackD,
                    new ReflectionFunction($this->callbackD)
                ),
                'methodE' => new CustomMethodDefinition(
                    true,
                    'methodE',
                    $this->callbackE,
                    new ReflectionFunction($this->callbackE)
                ),
                'methodF' => new CustomMethodDefinition(
                    true,
                    'methodF',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
                'next' => new RealMethodDefinition(new ReflectionMethod('Iterator::next'), 'next'),
                'rewind' => new RealMethodDefinition(new ReflectionMethod('Iterator::rewind'), 'rewind'),
                'testClassAMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodA'),
                    'testClassAMethodA'
                ),
                'testClassAMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                'testClassAMethodC' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodC'),
                    'testClassAMethodC'
                ),
                'testClassAMethodD' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodD'),
                    'testClassAMethodD'
                ),
                'testTraitBMethodA' => new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitB::testTraitBMethodA'),
                    'testTraitBMethodA'
                ),
                'testClassAStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
                'testClassAStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodB'),
                    'testClassAStaticMethodB'
                ),
                'testClassAStaticMethodC' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodC'),
                    'testClassAStaticMethodC'
                ),
                'testClassAStaticMethodD' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodD'),
                    'testClassAStaticMethodD'
                ),
                'testClassBMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBMethodA'),
                    'testClassBMethodA'
                ),
                'testClassBMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBMethodB'),
                    'testClassBMethodB'
                ),
                'testClassBStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBStaticMethodA'),
                    'testClassBStaticMethodA'
                ),
                'testClassBStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBStaticMethodB'),
                    'testClassBStaticMethodB'
                ),
                'valid' => new RealMethodDefinition(new ReflectionMethod('Iterator::valid'), 'valid'),
                '__call' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::__call'),
                    '__call'
                ),
                '__callStatic' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::__callStatic'),
                    '__callStatic'
                ),
            ),
            array(
                new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitB::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitB::testTraitBMethodA'),
                    'testTraitBMethodA'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitB::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
            )
        );
        $actual = $this->subject->methods();

        $this->assertEquals($expected, $actual);
        $this->assertSame($actual, $this->subject->methods());
    }

    public function testMethodsWithFinalMethods()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith(
            array(
                'Eloquent\Phony\Test\TestClassF',
                'Eloquent\Phony\Test\TestInterfaceG',
            )
        );

        $expected = new MethodDefinitionCollection(
            array(
                'methodA' => new CustomMethodDefinition(
                    false,
                    'methodA',
                    $this->callbackA,
                    new ReflectionFunction($this->callbackA)
                ),
                'methodB' => new CustomMethodDefinition(
                    false,
                    'methodB',
                    $this->callbackB,
                    new ReflectionFunction($this->callbackB)
                ),
                'methodC' => new CustomMethodDefinition(
                    false,
                    'methodC',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
                'methodD' => new CustomMethodDefinition(
                    true,
                    'methodD',
                    $this->callbackD,
                    new ReflectionFunction($this->callbackD)
                ),
                'methodE' => new CustomMethodDefinition(
                    true,
                    'methodE',
                    $this->callbackE,
                    new ReflectionFunction($this->callbackE)
                ),
                'methodF' => new CustomMethodDefinition(
                    true,
                    'methodF',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
            ),
            array()
        );
        $actual = $this->subject->methods();

        $this->assertEquals($expected, $actual);
        $this->assertSame($actual, $this->subject->methods());
    }

    public function testMethodsWithFinalMethodsAndTraits()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith(
            array(
                'Eloquent\Phony\Test\TestClassF',
                'Eloquent\Phony\Test\TestTraitI',
                'Eloquent\Phony\Test\TestInterfaceG',
            )
        );

        $expected = new MethodDefinitionCollection(
            array(
                'methodA' => new CustomMethodDefinition(
                    false,
                    'methodA',
                    $this->callbackA,
                    new ReflectionFunction($this->callbackA)
                ),
                'methodB' => new CustomMethodDefinition(
                    false,
                    'methodB',
                    $this->callbackB,
                    new ReflectionFunction($this->callbackB)
                ),
                'methodC' => new CustomMethodDefinition(
                    false,
                    'methodC',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
                'methodD' => new CustomMethodDefinition(
                    true,
                    'methodD',
                    $this->callbackD,
                    new ReflectionFunction($this->callbackD)
                ),
                'methodE' => new CustomMethodDefinition(
                    true,
                    'methodE',
                    $this->callbackE,
                    new ReflectionFunction($this->callbackE)
                ),
                'methodF' => new CustomMethodDefinition(
                    true,
                    'methodF',
                    function () {},
                    new ReflectionFunction(function () {})
                ),
            ),
            array(
                new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitI::testClassFStaticMethodA'),
                    'testClassFStaticMethodA'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestTraitI::testClassFMethodA'),
                    'testClassFMethodA'
                ),
            )
        );
        $actual = $this->subject->methods();

        $this->assertEquals($expected, $actual);
        $this->assertSame($actual, $this->subject->methods());
    }

    public function testIsEqualTo()
    {
        $this->setUpWith($this->typeNames);
        $definitionA = $this->subject;
        $this->setUpWith($this->typeNames);
        $definitionB = $this->subject;
        $definitionC = new MockDefinition(
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            null,
            $this->isTraitSupported,
            $this->isRelaxedKeywordsSupported
        );

        $this->assertTrue($definitionA->isEqualTo($definitionA));
        $this->assertTrue($definitionA->isEqualTo($definitionB));
        $this->assertTrue($definitionB->isEqualTo($definitionA));
        $this->assertTrue($definitionB->isEqualTo($definitionB));
        $this->assertTrue($definitionC->isEqualTo($definitionC));
        $this->assertFalse($definitionA->isEqualTo($definitionC));
        $this->assertFalse($definitionC->isEqualTo($definitionA));
        $this->assertFalse($definitionB->isEqualTo($definitionC));
        $this->assertFalse($definitionC->isEqualTo($definitionB));
    }

    public function testIsEqualToWithInequalSignature()
    {
        $definitionA = new MockDefinition(
            array(),
            array(
                'methodA' => array(function ($a, $b) {}, new ReflectionFunction(function ($a, $b) {})),
            ),
            array(),
            array(),
            array(),
            array(),
            null,
            $this->isTraitSupported,
            $this->isRelaxedKeywordsSupported
        );
        $definitionB = new MockDefinition(
            array(),
            array(
                'methodA' =>
                    array(function ($a, array $b = null) {}, new ReflectionFunction(function ($a, array $b = null) {})),
            ),
            array(),
            array(),
            array(),
            array(),
            null,
            $this->isTraitSupported,
            $this->isRelaxedKeywordsSupported
        );

        $this->assertFalse($definitionA->isEqualTo($definitionB));
    }

    public function testIsEqualToWithInequalSignatureStatic()
    {
        $definitionA = new MockDefinition(
            array(),
            array(),
            array(),
            array(
                'methodA' => array(function ($a, $b) {}, new ReflectionFunction(function ($a, $b) {})),
            ),
            array(),
            array(),
            null,
            $this->isTraitSupported,
            $this->isRelaxedKeywordsSupported
        );
        $definitionB = new MockDefinition(
            array(),
            array(),
            array(),
            array(
                'methodA' =>
                    array(function ($a, array $b = null) {}, new ReflectionFunction(function ($a, array $b = null) {})),
            ),
            array(),
            array(),
            null,
            $this->isTraitSupported,
            $this->isRelaxedKeywordsSupported
        );

        $this->assertFalse($definitionA->isEqualTo($definitionB));
    }
}
