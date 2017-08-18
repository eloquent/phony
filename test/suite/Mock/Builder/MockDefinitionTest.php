<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder;

use Countable;
use Eloquent\Phony\Mock\Builder\Method\CustomMethodDefinition;
use Eloquent\Phony\Mock\Builder\Method\MethodDefinitionCollection;
use Eloquent\Phony\Mock\Builder\Method\RealMethodDefinition;
use Eloquent\Phony\Mock\Builder\Method\TraitMethodDefinition;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestClassF;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestInterfaceB;
use Eloquent\Phony\Test\TestInterfaceG;
use Eloquent\Phony\Test\TestTraitA;
use Eloquent\Phony\Test\TestTraitB;
use Eloquent\Phony\Test\TestTraitI;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class MockDefinitionTest extends TestCase
{
    protected function setUp()
    {
        $this->featureDetector = new FeatureDetector();

        $this->typeNames = [
            Countable::class,
            TestClassB::class,
            TestInterfaceA::class,
            TestInterfaceB::class,
            Iterator::class,
        ];
        $this->typeNamesTraits = [
            Countable::class,
            TestClassB::class,
            TestInterfaceA::class,
            TestInterfaceB::class,
            TestTraitA::class,
            TestTraitB::class,
            Iterator::class,
        ];
        $this->parentClassName = TestClassB::class;
        $this->interfaceNames = [
            Countable::class,
            TestInterfaceA::class,
            TestInterfaceB::class,
            Iterator::class,
        ];
        $this->traitNames = [
            TestTraitA::class,
            TestTraitB::class,
        ];

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
        $this->types = [];

        foreach ($typeNames as $typeName) {
            $this->types[strtolower($typeName)] = new ReflectionClass($typeName);
        }

        $this->customMethods = [
            'methodA' => [$this->callbackA, $this->callbackReflectorA],
            'methodB' => [$this->callbackB, $this->callbackReflectorB],
            'methodC' => [$this->callbackC, $this->callbackReflectorC],
        ];
        $this->customProperties = ['a' => 'b', 'c' => 'd'];
        $this->customStaticMethods = [
            'methodD' => [$this->callbackD, $this->callbackReflectorD],
            'methodE' => [$this->callbackE, $this->callbackReflectorE],
            'methodF' => [$this->callbackF, $this->callbackReflectorF],
        ];
        $this->customStaticProperties = ['e' => 'f', 'g' => 'h'];
        $this->customConstants = ['i' => 'j', 'k' => 'l'];
        $this->className = 'ClassName';
        $this->subject = new MockDefinition(
            $this->types,
            $this->customMethods,
            $this->customProperties,
            $this->customStaticMethods,
            $this->customStaticProperties,
            $this->customConstants,
            $this->className
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
        $this->assertSame([], $this->subject->traitNames());
    }

    public function testConstructorWithTraits()
    {
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
            [
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
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodA'),
                    'testClassAMethodA'
                ),
                'testClassAMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                'testClassAMethodC' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodC'),
                    'testClassAMethodC'
                ),
                'testClassAMethodD' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodD'),
                    'testClassAMethodD'
                ),
                'testClassAStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
                'testClassAStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodB'),
                    'testClassAStaticMethodB'
                ),
                'testClassAStaticMethodC' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodC'),
                    'testClassAStaticMethodC'
                ),
                'testClassAStaticMethodD' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodD'),
                    'testClassAStaticMethodD'
                ),
                'testClassBMethodA' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBMethodA'),
                    'testClassBMethodA'
                ),
                'testClassBMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBMethodB'),
                    'testClassBMethodB'
                ),
                'testClassBStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBStaticMethodA'),
                    'testClassBStaticMethodA'
                ),
                'testClassBStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBStaticMethodB'),
                    'testClassBStaticMethodB'
                ),
                'valid' => new RealMethodDefinition(new ReflectionMethod('Iterator::valid'), 'valid'),
                '__call' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::__call'),
                    '__call'
                ),
                '__callStatic' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::__callStatic'),
                    '__callStatic'
                ),
            ],
            []
        );
        $actual = $this->subject->methods();

        $this->assertEquals($expected, $actual);
        $this->assertSame($actual, $this->subject->methods());
    }

    public function testMethodsWithTraits()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith($this->typeNamesTraits);

        $expected = new MethodDefinitionCollection(
            [
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
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodA'),
                    'testClassAMethodA'
                ),
                'testClassAMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                'testClassAMethodC' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodC'),
                    'testClassAMethodC'
                ),
                'testClassAMethodD' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAMethodD'),
                    'testClassAMethodD'
                ),
                'testTraitBMethodA' => new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitB::class . '::testTraitBMethodA'),
                    'testTraitBMethodA'
                ),
                'testClassAStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
                'testClassAStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodB'),
                    'testClassAStaticMethodB'
                ),
                'testClassAStaticMethodC' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodC'),
                    'testClassAStaticMethodC'
                ),
                'testClassAStaticMethodD' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassAStaticMethodD'),
                    'testClassAStaticMethodD'
                ),
                'testClassBMethodA' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBMethodA'),
                    'testClassBMethodA'
                ),
                'testClassBMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBMethodB'),
                    'testClassBMethodB'
                ),
                'testClassBStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBStaticMethodA'),
                    'testClassBStaticMethodA'
                ),
                'testClassBStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::testClassBStaticMethodB'),
                    'testClassBStaticMethodB'
                ),
                'valid' => new RealMethodDefinition(new ReflectionMethod('Iterator::valid'), 'valid'),
                '__call' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::__call'),
                    '__call'
                ),
                '__callStatic' => new RealMethodDefinition(
                    new ReflectionMethod(TestClassB::class . '::__callStatic'),
                    '__callStatic'
                ),
            ],
            [
                new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitA::class . '::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitA::class . '::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitB::class . '::testClassAMethodB'),
                    'testClassAMethodB'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitB::class . '::testTraitBMethodA'),
                    'testTraitBMethodA'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitB::class . '::testClassAStaticMethodA'),
                    'testClassAStaticMethodA'
                ),
            ]
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
            [
                TestClassF::class,
                TestInterfaceG::class,
            ]
        );

        $expected = new MethodDefinitionCollection(
            [
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
            ],
            []
        );
        $actual = $this->subject->methods();

        $this->assertEquals($expected, $actual);
        $this->assertSame($actual, $this->subject->methods());
    }

    public function testMethodsWithFinalMethodsAndTraits()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith(
            [
                TestClassF::class,
                TestTraitI::class,
                TestInterfaceG::class,
            ]
        );

        $expected = new MethodDefinitionCollection(
            [
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
            ],
            [
                new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitI::class . '::testClassFStaticMethodA'),
                    'testClassFStaticMethodA'
                ),
                new TraitMethodDefinition(
                    new ReflectionMethod(TestTraitI::class . '::testClassFMethodA'),
                    'testClassFMethodA'
                ),
            ]
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
            [],
            [],
            [],
            [],
            [],
            [],
            null
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
            [],
            [
                'methodA' => [function ($a, $b) {}, new ReflectionFunction(function ($a, $b) {})],
            ],
            [],
            [],
            [],
            [],
            null
        );
        $definitionB = new MockDefinition(
            [],
            [
                'methodA' =>
                    [function ($a, array $b = null) {}, new ReflectionFunction(function ($a, array $b = null) {})],
            ],
            [],
            [],
            [],
            [],
            null
        );

        $this->assertFalse($definitionA->isEqualTo($definitionB));
    }

    public function testIsEqualToWithInequalSignatureStatic()
    {
        $definitionA = new MockDefinition(
            [],
            [],
            [],
            [
                'methodA' => [function ($a, $b) {}, new ReflectionFunction(function ($a, $b) {})],
            ],
            [],
            [],
            null
        );
        $definitionB = new MockDefinition(
            [],
            [],
            [],
            [
                'methodA' =>
                    [function ($a, array $b = null) {}, new ReflectionFunction(function ($a, array $b = null) {})],
            ],
            [],
            [],
            null
        );

        $this->assertFalse($definitionA->isEqualTo($definitionB));
    }
}
