<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Mock\Builder\Definition\Method\CustomMethodDefinition;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionCollection;
use Eloquent\Phony\Mock\Builder\Definition\Method\RealMethodDefinition;
use Eloquent\Phony\Mock\Factory\MockFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionMethod;

class MockBuilderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->inputTypes = array('Eloquent\Phony\Test\TestClassB', 'Iterator', 'Countable');
        $this->callbackA = function () {};
        $this->callbackB = function () {};
        $this->callbackC = function () {};
        $this->callbackD = function () {};
        $this->callbackE = function () {};
        $this->definition = array(
            'static methodA' => $this->callbackA,
            'static methodB' => $this->callbackB,
            'static propertyA' => 'valueA',
            'static propertyB' => 'valueB',
            'methodC' => $this->callbackC,
            'methodD' => $this->callbackD,
            'propertyC' => 'valueC',
            'propertyD' => 'valueD',
            'const constantA' => 'constantValueA',
            'const constantB' => 'constantValueB',
        );
        $this->className = 'ClassName';
        $this->id = 111;
        $this->factory = new MockFactory();
        $this->subject =
            new MockBuilder($this->inputTypes, $this->definition, $this->className, $this->id, $this->factory);

        $this->types = $this->inputTypes;
        $this->reflectors = array();
        foreach ($this->types as $type) {
            $this->reflectors[$type] = new ReflectionClass($type);
        }
    }

    public function testConstructor()
    {
        $this->assertSame($this->types, $this->subject->types());
        $this->assertEquals($this->reflectors, $this->subject->reflectors());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->id, $this->subject->id());
        $this->assertSame($this->factory, $this->subject->factory());
        $this->assertSame('Eloquent\Phony\Test\TestClassB', $this->subject->parentClassName());
        $this->assertSame(array('Iterator', 'Countable'), $this->subject->interfaceNames());
        $this->assertSame(array(), $this->subject->traitNames());
        $this->assertSame(
            array('methodA' => $this->callbackA, 'methodB' => $this->callbackB),
            $this->subject->staticMethods()
        );
        $this->assertSame(
            array('methodC' => $this->callbackC, 'methodD' => $this->callbackD),
            $this->subject->methods()
        );
        $this->assertSame(array('propertyA' => 'valueA', 'propertyB' => 'valueB'), $this->subject->staticProperties());
        $this->assertSame(
            array('propertyC' => 'valueC', 'propertyD' => 'valueD'),
            $this->subject->properties()
        );
        $this->assertSame(
            array('constantA' => 'constantValueA', 'constantB' => 'constantValueB'),
            $this->subject->constants()
        );
        $this->assertNull($this->subject->methodDefinitions());
    }

    public function testConstructorWithoutClassName()
    {
        $this->subject = new MockBuilder(null, null, null, $this->id);

        $this->assertSame('PhonyMock_111', $this->subject->className());
    }

    public function testConstructorWithDuplicateTypes()
    {
        $this->subject = new MockBuilder(
            array(
                'Eloquent\Phony\Test\TestClassB',
                'Iterator',
                'Countable',
                'Eloquent\Phony\Test\TestClassB',
                'Iterator',
                'Countable',
            )
        );

        $this->assertSame($this->types, $this->subject->types());
        $this->assertEquals($this->reflectors, $this->subject->reflectors());
    }

    /**
     * @requires PHP 5.4.0-dev
     */
    public function testConstructorWithTraits()
    {
        $this->inputTypes = array(
            'Eloquent\Phony\Test\TestClassB',
            'Iterator',
            'Countable',
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
            'Eloquent\Phony\Test\TestClassB',
            'Iterator',
            'Countable',
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
        );
        $this->types = array(
            'Eloquent\Phony\Test\TestClassB',
            'Iterator',
            'Countable',
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
        );
        $this->reflectors = array();
        foreach ($this->types as $type) {
            $this->reflectors[$type] = new ReflectionClass($type);
        }
        $this->subject = new MockBuilder($this->inputTypes, $this->definition, $this->className, $this->id);

        $this->assertSame($this->types, $this->subject->types());
        $this->assertEquals($this->reflectors, $this->subject->reflectors());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->id, $this->subject->id());
        $this->assertSame('Eloquent\Phony\Test\TestClassB', $this->subject->parentClassName());
        $this->assertSame(array('Iterator', 'Countable'), $this->subject->interfaceNames());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestTraitA', 'Eloquent\Phony\Test\TestTraitB'),
            $this->subject->traitNames()
        );
        $this->assertSame(
            array('methodA' => $this->callbackA, 'methodB' => $this->callbackB),
            $this->subject->staticMethods()
        );
        $this->assertSame(
            array('methodC' => $this->callbackC, 'methodD' => $this->callbackD),
            $this->subject->methods()
        );
        $this->assertSame(array('propertyA' => 'valueA', 'propertyB' => 'valueB'), $this->subject->staticProperties());
        $this->assertSame(
            array('propertyC' => 'valueC', 'propertyD' => 'valueD'),
            $this->subject->properties()
        );
        $this->assertSame(
            array('constantA' => 'constantValueA', 'constantB' => 'constantValueB'),
            $this->subject->constants()
        );
        $this->assertNull($this->subject->methodDefinitions());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockBuilder();

        $this->assertSame(array(), $this->subject->types());
        $this->assertSame(array(), $this->subject->reflectors());
        $this->assertRegExp('/^PhonyMock_[[:xdigit:]]{6}$/', $this->subject->className());
        $this->assertNull($this->subject->id());
        $this->assertSame(MockFactory::instance(), $this->subject->factory());
        $this->assertNull($this->subject->parentClassName());
        $this->assertSame(array(), $this->subject->interfaceNames());
        $this->assertSame(array(), $this->subject->traitNames());
        $this->assertSame(array(), $this->subject->methods());
        $this->assertSame(array(), $this->subject->staticMethods());
        $this->assertSame(array(), $this->subject->properties());
        $this->assertSame(array(), $this->subject->staticProperties());
        $this->assertSame(array(), $this->subject->constants());
    }

    public function testConstructorFailureInvalidClassName()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\InvalidClassNameException');
        new MockBuilder(null, null, '1');
    }

    public function testConstructorFailureUndefinedClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\InvalidTypeException');
        new MockBuilder(array('Nonexistent'));
    }

    public function testConstructorFailureFinalClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\FinalClassException');
        new MockBuilder(array('Eloquent\Phony\Test\TestFinalClass'));
    }

    public function testConstructorFailureMultipleInheritance()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\MultipleInheritanceException');
        new MockBuilder(array('Eloquent\Phony\Test\TestClassB', 'ArrayIterator'));
    }

    public function testConstructorFailureInvalidType()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\InvalidTypeException');
        new MockBuilder(array(1));
    }

    public function testLikeWithString()
    {
        $builder = new MockBuilder();
        $types = array('Iterator', 'Countable', 'Serializable');

        $this->assertSame($builder, $builder->like('Iterator', array('Countable', 'Serializable')));
        $this->assertSame($types, $builder->types());
    }

    public function testLikeWithObject()
    {
        $builder = new MockBuilder();
        $types = array('stdClass');

        $this->assertSame($builder, $builder->like((object) array()));
        $this->assertSame($types, $builder->types());
    }

    public function testLikeWithBuilder()
    {
        $builder = new MockBuilder();

        $this->assertSame($builder, $builder->like($this->subject));
        $this->assertSame($this->types, $builder->types());
    }

    public function testLikeFailureUndefinedClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\InvalidTypeException');
        $this->subject->like('Nonexistent');
    }

    public function testLikeFailureFinalClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\FinalClassException');
        $this->subject->like('Eloquent\Phony\Test\TestFinalClass');
    }

    public function testLikeFailureMultipleInheritance()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\MultipleInheritanceException');
        $this->subject->like('Eloquent\Phony\Test\TestClassB', 'ArrayIterator');
    }

    public function testLikeFailureInvalidType()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\InvalidTypeException');
        $this->subject->like(1);
    }

    public function testLikeFailureFinalized()
    {
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\FinalizedMockException');
        $this->subject->like('ClassName');
    }

    public function testDefine()
    {
        $this->subject = new MockBuilder();
        $this->definition = array(
            'static methodA' => $this->callbackA,
            'static methodB' => $this->callbackB,
            'static propertyA' => 'valueA',
            'static propertyB' => 'valueB',
            'methodC' => $this->callbackC,
            'methodD' => $this->callbackD,
            'propertyC' => 'valueC',
            'var propertyD' => $this->callbackE,
            'const constantA' => 'constantValueA',
            'const constantB' => 'constantValueB',
        );

        $this->assertSame($this->subject, $this->subject->define($this->definition));
        $this->assertSame(
            array('methodA' => $this->callbackA, 'methodB' => $this->callbackB),
            $this->subject->staticMethods()
        );
        $this->assertSame(
            array('methodC' => $this->callbackC, 'methodD' => $this->callbackD),
            $this->subject->methods()
        );
        $this->assertSame(array('propertyA' => 'valueA', 'propertyB' => 'valueB'), $this->subject->staticProperties());
        $this->assertSame(
            array('propertyC' => 'valueC', 'propertyD' => $this->callbackE),
            $this->subject->properties()
        );
        $this->assertSame(
            array('constantA' => 'constantValueA', 'constantB' => 'constantValueB'),
            $this->subject->constants()
        );
    }

    public function testDefineWithObject()
    {
        $this->subject = new MockBuilder();
        $this->definition = array(
            'static methodA' => $this->callbackA,
            'static methodB' => $this->callbackB,
            'static propertyA' => 'valueA',
            'static propertyB' => 'valueB',
            'methodC' => $this->callbackC,
            'methodD' => $this->callbackD,
            'propertyC' => 'valueC',
            'var propertyD' => $this->callbackE,
            'const constantA' => 'constantValueA',
            'const constantB' => 'constantValueB',
        );

        $this->assertSame($this->subject, $this->subject->define((object) $this->definition));
        $this->assertSame(
            array('methodA' => $this->callbackA, 'methodB' => $this->callbackB),
            $this->subject->staticMethods()
        );
        $this->assertSame(
            array('methodC' => $this->callbackC, 'methodD' => $this->callbackD),
            $this->subject->methods()
        );
        $this->assertSame(array('propertyA' => 'valueA', 'propertyB' => 'valueB'), $this->subject->staticProperties());
        $this->assertSame(
            array('propertyC' => 'valueC', 'propertyD' => $this->callbackE),
            $this->subject->properties()
        );
        $this->assertSame(
            array('constantA' => 'constantValueA', 'constantB' => 'constantValueB'),
            $this->subject->constants()
        );
    }

    public function testAddMethod()
    {
        $this->subject = new MockBuilder();
        $callback = function () {};

        $this->assertSame($this->subject, $this->subject->addMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addMethod('methodB'));
        $this->assertSame(array('methodA' => $callback, 'methodB' => null), $this->subject->methods());
    }

    public function testAddStaticMethod()
    {
        $this->subject = new MockBuilder();
        $callback = function () {};

        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodB'));
        $this->assertSame(array('methodA' => $callback, 'methodB' => null), $this->subject->staticMethods());
    }

    public function testAddProperty()
    {
        $this->subject = new MockBuilder();
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addProperty('propertyB'));
        $this->assertSame(array('propertyA' => $value, 'propertyB' => null), $this->subject->properties());
    }

    public function testAddStaticProperty()
    {
        $this->subject = new MockBuilder();
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyB'));
        $this->assertSame(array('propertyA' => $value, 'propertyB' => null), $this->subject->staticProperties());
    }

    public function testAddConstant()
    {
        $this->subject = new MockBuilder();
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addConstant('CONSTANT_NAME', $value));
        $this->assertSame(array('CONSTANT_NAME' => $value), $this->subject->constants());
    }

    public function testNamed()
    {
        $this->className = 'AnotherClassName';

        $this->assertSame($this->subject, $this->subject->named($this->className));
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->subject, $this->subject->named());
        $this->assertSame('PhonyMock_TestClassB_111', $this->subject->className());
    }

    public function testNamedFailureInvalid()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\InvalidClassNameException');
        $this->subject->named('1');
    }

    public function testNamedFailureFinalized()
    {
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\FinalizedMockException');
        $this->subject->named('AnotherClassName');
    }

    public function testFinalize()
    {
        $expected = new MethodDefinitionCollection(
            array(
                'count' => new RealMethodDefinition(new ReflectionMethod('Countable::count')),
                'current' => new RealMethodDefinition(new ReflectionMethod('Iterator::current')),
                'key' => new RealMethodDefinition(new ReflectionMethod('Iterator::key')),
                'methodA' => new CustomMethodDefinition(true, 'methodA', $this->callbackA),
                'methodB' => new CustomMethodDefinition(true, 'methodB', $this->callbackB),
                'methodC' => new CustomMethodDefinition(false, 'methodC', $this->callbackC),
                'methodD' => new CustomMethodDefinition(false, 'methodD', $this->callbackD),
                'next' => new RealMethodDefinition(new ReflectionMethod('Iterator::next')),
                'rewind' => new RealMethodDefinition(new ReflectionMethod('Iterator::rewind')),
                'testClassAMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodA')
                ),
                'testClassAMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodB')
                ),
                'testClassAMethodC' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodC')
                ),
                'testClassAMethodD' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAMethodD')
                ),
                'testClassAStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodA')
                ),
                'testClassAStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodB')
                ),
                'testClassAStaticMethodC' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodC')
                ),
                'testClassAStaticMethodD' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassAStaticMethodD')
                ),
                'testClassBMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBMethodA')
                ),
                'testClassBMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBMethodB')
                ),
                'testClassBStaticMethodA' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBStaticMethodA')
                ),
                'testClassBStaticMethodB' => new RealMethodDefinition(
                    new ReflectionMethod('Eloquent\Phony\Test\TestClassB::testClassBStaticMethodB')
                ),
                'valid' => new RealMethodDefinition(new ReflectionMethod('Iterator::valid')),
            )
        );

        $this->assertFalse($this->subject->isFinalized());
        $this->assertSame($this->subject, $this->subject->finalize());
        $this->assertTrue($this->subject->isFinalized());
        $this->assertSame($this->subject, $this->subject->finalize());
        $this->assertTrue($this->subject->isFinalized());
        $this->assertEquals($expected, $this->subject->methodDefinitions());
    }

    public function testStubProxying()
    {
        $this->subject = new MockBuilder('Eloquent\Phony\Test\TestClassA');

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $this->subject->testClassAMethodA());
    }

    public function classNameGenerationData()
    {
        //                                      like                              expected
        return array(
            'Anonymous'                => array(null,                             'PhonyMock_111'),
            'Extends class'            => array('stdClass',                       'PhonyMock_stdClass_111'),
            'Extends namespaced class' => array('Eloquent\Phony\Test\TestClassB', 'PhonyMock_TestClassB_111'),
            'Inherits interface'       => array(array('Iterator', 'Countable'),   'PhonyMock_Iterator_111'),
        );
    }

    /**
     * @dataProvider classNameGenerationData
     */
    public function testClassNameGeneration($like, $expected)
    {
        $this->subject = new MockBuilder($like, null, null, 111);

        $this->assertSame($expected, $this->subject->className());
    }

    /**
     * @requires PHP 5.4.0-dev
     */
    public function testClassNameGenerationWithTraits()
    {
        $this->subject = new MockBuilder(
            array('Eloquent\Phony\Test\TestTraitA', 'Eloquent\Phony\Test\TestTraitB'),
            null,
            null,
            111
        );

        $this->assertSame('PhonyMock_TestTraitA_111', $this->subject->className());
    }
}
