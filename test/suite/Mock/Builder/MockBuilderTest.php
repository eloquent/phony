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
use Eloquent\Phony\Mock\Builder\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class MockBuilderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->inputTypes = array(
            'Eloquent\Phony\Test\TestClassB',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Iterator',
            'Countable'
        );
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
        $this->factory = new MockFactory(new Sequencer());
        $this->subject = new MockBuilder($this->inputTypes, $this->definition, null, null, $this->factory);

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
        $this->assertNull($this->subject->id());
        $this->assertSame($this->factory, $this->subject->factory());
        $this->assertSame('Eloquent\Phony\Test\TestClassB', $this->subject->parentClassName());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestInterfaceA', 'Iterator', 'Countable'),
            $this->subject->interfaceNames()
        );
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
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorWithDuplicateTypes()
    {
        $this->subject = new MockBuilder(
            array(
                'Eloquent\Phony\Test\TestClassB',
                'Eloquent\Phony\Test\TestInterfaceA',
                'Iterator',
                'Countable',
                'Eloquent\Phony\Test\TestClassB',
                'Eloquent\Phony\Test\TestInterfaceA',
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
            'Eloquent\Phony\Test\TestInterfaceA',
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
            'Eloquent\Phony\Test\TestInterfaceA',
            'Iterator',
            'Countable',
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
        );
        $this->reflectors = array();
        foreach ($this->types as $type) {
            $this->reflectors[$type] = new ReflectionClass($type);
        }
        $this->subject = new MockBuilder($this->inputTypes, $this->definition, null, null, $this->factory);

        $this->assertSame($this->types, $this->subject->types());
        $this->assertEquals($this->reflectors, $this->subject->reflectors());
        $this->assertNull($this->subject->id());
        $this->assertSame($this->factory, $this->subject->factory());
        $this->assertSame('Eloquent\Phony\Test\TestClassB', $this->subject->parentClassName());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestInterfaceA', 'Iterator', 'Countable'),
            $this->subject->interfaceNames()
        );
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
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
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
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorWithClassName()
    {
        $this->subject = new MockBuilder($this->inputTypes, $this->definition, 'ClassName', null, $this->factory);

        $this->assertSame('ClassName', $this->subject->className());
    }

    public function testConstructorWithId()
    {
        $this->subject = new MockBuilder($this->inputTypes, $this->definition, null, 111, $this->factory);

        $this->assertSame('PhonyMock_TestClassB_111', $this->subject->className());
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
        $this->definition = (object) array(
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

    public function testDefineFailureInvalid()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\InvalidDefinitionException');
        $this->subject->define(array('propertyA', 'valueA'));
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
        $this->assertRegExp('/^PhonyMock_TestClassB_[[:xdigit:]]{6}$/', $this->subject->className());
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

    public function testFinalize()
    {
        $this->assertFalse($this->subject->isFinalized());
        $this->assertSame($this->subject, $this->subject->finalize());
        $this->assertTrue($this->subject->isFinalized());
        $this->assertSame($this->subject, $this->subject->finalize());
        $this->assertTrue($this->subject->isFinalized());
    }

    public function testMethodDefinitions()
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

        $this->assertEquals($expected, $this->subject->methodDefinitions());
        $this->assertTrue($this->subject->isFinalized());
    }

    public function testBuild()
    {
        $actual = $this->subject->build();

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('ReflectionClass', $actual);
        $this->assertTrue($actual->implementsInterface('Eloquent\Phony\Mock\MockInterface'));
        $this->assertTrue($actual->isSubclassOf('Eloquent\Phony\Test\TestClassB'));
    }

    public function testBuildFailure()
    {
        $this->subject->build();
        $builder = new MockBuilder(null, null, $this->subject->className());
        $exception = null;
        try {
            $builder->build();
        } catch (ClassExistsException $exception) {}

        $this->assertNotNull($exception);
        $this->assertFalse($builder->isFinalized());
        $this->assertFalse($builder->isBuilt());
    }

    public function testGet()
    {
        $actual = $this->subject->get();

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertSame($actual, $this->subject->get());
    }

    public function testCreate()
    {
        $first = $this->subject->create('a', 'b');

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertSame(array('a', 'b'), $first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->create();

        $this->assertNotSame($first, $second);
        $this->assertSame(array(), $second->constructorArguments);
        $this->assertSame($second, $this->subject->get());
    }

    public function testCreateWith()
    {
        $first = $this->subject->createWith(array('a', 'b'), 'id');
        $idProperty = new ReflectionProperty($this->subject->className(), '_mockId');
        $idProperty->setAccessible(true);

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertSame('id', $idProperty->getValue($first));
        $this->assertSame(array('a', 'b'), $first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->createWith(array());

        $this->assertNotSame($first, $second);
        $this->assertSame('0', $idProperty->getValue($second));
        $this->assertSame(array(), $second->constructorArguments);
        $this->assertSame($second, $this->subject->get());

        $third = $this->subject->createWith();

        $this->assertNotSame($first, $third);
        $this->assertNotSame($second, $third);
        $this->assertSame('1', $idProperty->getValue($third));
        $this->assertNull($third->constructorArguments);
        $this->assertSame($third, $this->subject->get());
    }

    public function testFull()
    {
        $actual = $this->subject->full();

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertNull($actual->testClassAMethodA());
        $this->assertNull($actual->testClassAMethodB('a', 'b'));
        $this->assertSame($actual, $this->subject->get());
    }

    public function testStaticStub()
    {
        $actual = $this->subject->staticStub('testClassAStaticMethodA')->with('a', 'b')->returns('x');
        $class = $this->subject->className();

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame('x', $class::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('cd', $class::testClassAStaticMethodA('c', 'd'));
    }

    public function testStaticStubFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\UndefinedMethodStubException');
        $this->subject->staticStub('nonexistent');
    }

    public function testStub()
    {
        $actual = $this->subject->stub('testClassAMethodA')->with('a', 'b')->returns('x');

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame('x', $this->subject->get()->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $this->subject->get()->testClassAMethodA('c', 'd'));
    }

    public function testStubFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Builder\Exception\UndefinedMethodStubException');
        $this->subject->stub('nonexistent');
    }

    public function testMagicCall()
    {
        $actual = $this->subject->testClassAMethodA('a', 'b')->returns('x');

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame('x', $this->subject->get()->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $this->subject->get()->testClassAMethodA('c', 'd'));
    }

    public function testMagicCallFailureUndefined()
    {
        $this->setExpectedException(
            'BadMethodCallException',
            "Call to undefined method Eloquent\Phony\Mock\Builder\MockBuilder::nonexistent()."
        );
        $this->subject->nonexistent();
    }
}
