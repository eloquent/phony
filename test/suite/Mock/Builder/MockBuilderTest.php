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

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockBuilderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->signatureInspector = new FunctionSignatureInspector();
        $this->featureDetector = new FeatureDetector();

        $this->typeNames = array(
            'Eloquent\Phony\Test\TestClassB',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Iterator',
            'Countable',
        );
        $this->typeNamesTraits = array(
            'Eloquent\Phony\Test\TestClassB',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Iterator',
            'Countable',
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
        );

        $this->callbackA = function () {};
        $this->callbackB = function () {};
        $this->callbackC = function () {};
        $this->callbackD = function () {};
        $this->callbackE = function () {};
    }

    protected function setUpWith($typeNames)
    {
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
        $this->proxyFactory = new ProxyFactory();
        $this->subject = new MockBuilder(
            $typeNames,
            $this->definition,
            null,
            $this->factory,
            $this->proxyFactory,
            $this->signatureInspector,
            $this->featureDetector
        );
    }

    protected function typesFor($typeNames)
    {
        $types = array();

        foreach ($typeNames as $typeName) {
            $types[$typeName] = new ReflectionClass($typeName);
        }

        return $types;
    }

    public function testConstructor()
    {
        $this->setUpWith($this->typeNames);

        $this->assertEquals($this->typesFor($this->typeNames), $this->subject->types());
        $this->assertSame($this->factory, $this->subject->factory());
        $this->assertSame($this->proxyFactory, $this->subject->proxyFactory());
        $this->assertSame($this->signatureInspector, $this->subject->signatureInspector());
        $this->assertSame($this->featureDetector, $this->subject->featureDetector());
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorWithDuplicateTypes()
    {
        $this->setUpWith(
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

        $this->assertEquals($this->typesFor($this->typeNames), $this->subject->types());
    }

    public function testConstructorWithTraits()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->setUpWith(
            array(
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
            )
        );

        $this->assertEquals($this->typesFor($this->typeNamesTraits), $this->subject->types());
        $this->assertSame($this->factory, $this->subject->factory());
        $this->assertSame($this->proxyFactory, $this->subject->proxyFactory());
        $this->assertSame($this->featureDetector, $this->subject->featureDetector());
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockBuilder();

        $this->assertSame(array(), $this->subject->types());
        $this->assertSame(MockFactory::instance(), $this->subject->factory());
        $this->assertSame(ProxyFactory::instance(), $this->subject->proxyFactory());
        $this->assertSame(FunctionSignatureInspector::instance(), $this->subject->signatureInspector());
        $this->assertSame(FeatureDetector::instance(), $this->subject->featureDetector());
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorWithClassName()
    {
        $this->subject = new MockBuilder($this->typesFor($this->typeNames), null, 'ClassName');
        $definition = $this->subject->definition();

        $this->assertSame('ClassName', $definition->className());
    }

    public function testConstructorFailureInvalidClassName()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidClassNameException');
        new MockBuilder(null, null, '1');
    }

    public function testConstructorFailureUndefinedClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        new MockBuilder(array('Nonexistent'));
    }

    public function testConstructorFailureFinalClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalClassException');
        new MockBuilder(array('Eloquent\Phony\Test\TestFinalClass'));
    }

    public function testConstructorFailureMultipleInheritance()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        new MockBuilder(array('Eloquent\Phony\Test\TestClassB', 'ArrayIterator'));
    }

    public function testConstructorFailureInvalidType()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        new MockBuilder(array(1));
    }

    public function testLikeWithString()
    {
        $builder = new MockBuilder();
        $typeNames = array('Iterator', 'Countable', 'Serializable');

        $this->assertSame($builder, $builder->like('Iterator', array('Countable', 'Serializable')));
        $this->assertEquals($this->typesFor($typeNames), $builder->types());
    }

    public function testLikeWithReflectionClass()
    {
        $builder = new MockBuilder();
        $typeNames = array('stdClass');

        $this->assertSame($builder, $builder->like(new ReflectionClass('stdClass')));
        $this->assertEquals($this->typesFor($typeNames), $builder->types());
    }

    public function testLikeWithBuilder()
    {
        $this->setUpWith($this->typeNames);
        $builder = new MockBuilder();

        $this->assertSame($builder, $builder->like($this->subject));
        $this->assertSame($this->subject->types(), $builder->types());
    }

    public function testLikeFailureUndefinedClass()
    {
        $this->subject = new MockBuilder();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like('Nonexistent');
    }

    public function testLikeFailureFinalClass()
    {
        $this->subject = new MockBuilder();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalClassException');
        $this->subject->like('Eloquent\Phony\Test\TestFinalClass');
    }

    public function testLikeFailureMultipleInheritance()
    {
        $this->subject = new MockBuilder();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->subject->like('Eloquent\Phony\Test\TestClassB', 'ArrayIterator');
    }

    public function testLikeFailureMultipleInheritanceOnSubsequentCall()
    {
        $this->subject = new MockBuilder('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->subject->like('Eloquent\Phony\Test\TestClassB', 'ArrayIterator');
    }

    public function testLikeFailureInvalidType()
    {
        $this->subject = new MockBuilder();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like(1);
    }

    public function testLikeFailureFinalized()
    {
        $this->subject = new MockBuilder();
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
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

        $definition = $this->subject->definition();

        $this->assertSame(
            array('methodA' => $this->callbackA, 'methodB' => $this->callbackB),
            $definition->customStaticMethods()
        );
        $this->assertSame(
            array('methodC' => $this->callbackC, 'methodD' => $this->callbackD),
            $definition->customMethods()
        );
        $this->assertSame(
            array('propertyA' => 'valueA', 'propertyB' => 'valueB'),
            $definition->customStaticProperties()
        );
        $this->assertSame(
            array('propertyC' => 'valueC', 'propertyD' => $this->callbackE),
            $definition->customProperties()
        );
        $this->assertSame(
            array('constantA' => 'constantValueA', 'constantB' => 'constantValueB'),
            $definition->customConstants()
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

        $definition = $this->subject->definition();

        $this->assertSame(
            array('methodA' => $this->callbackA, 'methodB' => $this->callbackB),
            $definition->customStaticMethods()
        );
        $this->assertSame(
            array('methodC' => $this->callbackC, 'methodD' => $this->callbackD),
            $definition->customMethods()
        );
        $this->assertSame(
            array('propertyA' => 'valueA', 'propertyB' => 'valueB'),
            $definition->customStaticProperties()
        );
        $this->assertSame(
            array('propertyC' => 'valueC', 'propertyD' => $this->callbackE),
            $definition->customProperties()
        );
        $this->assertSame(
            array('constantA' => 'constantValueA', 'constantB' => 'constantValueB'),
            $definition->customConstants()
        );
    }

    public function testDefineFailureInvalid()
    {
        $this->subject = new MockBuilder();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidDefinitionException');
        $this->subject->define(array('propertyA', 'valueA'));
    }

    public function testAddMethod()
    {
        $this->subject = new MockBuilder();
        $callback = function () {};

        $this->assertSame($this->subject, $this->subject->addMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addMethod('methodB'));

        $definition = $this->subject->definition();

        $this->assertSame(array('methodA' => $callback, 'methodB' => null), $definition->customMethods());
    }

    public function testAddMethodFailureFinalized()
    {
        $this->subject = new MockBuilder();
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addMethod('methodA', function () {});
    }

    public function testAddStaticMethod()
    {
        $this->subject = new MockBuilder();
        $callback = function () {};

        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodB'));

        $definition = $this->subject->definition();

        $this->assertSame(array('methodA' => $callback, 'methodB' => null), $definition->customStaticMethods());
    }

    public function testAddStaticMethodFailureFinalized()
    {
        $this->subject = new MockBuilder();
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addStaticMethod('methodA', function () {});
    }

    public function testAddProperty()
    {
        $this->subject = new MockBuilder();
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame(array('propertyA' => $value, 'propertyB' => null), $definition->customProperties());
    }

    public function testAddPropertyFailureFinalized()
    {
        $this->subject = new MockBuilder();
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addProperty('propertyA');
    }

    public function testAddStaticProperty()
    {
        $this->subject = new MockBuilder();
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame(array('propertyA' => $value, 'propertyB' => null), $definition->customStaticProperties());
    }

    public function testAddStaticPropertyFailureFinalized()
    {
        $this->subject = new MockBuilder();
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addStaticProperty('propertyA');
    }

    public function testAddConstant()
    {
        $this->subject = new MockBuilder();
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addConstant('CONSTANT_NAME', $value));

        $definition = $this->subject->definition();

        $this->assertSame(array('CONSTANT_NAME' => $value), $definition->customConstants());
    }

    public function testAddConstantFailureFinalized()
    {
        $this->subject = new MockBuilder();
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addConstant('CONSTANT_NAME', 'value');
    }

    public function testNamed()
    {
        $this->subject = new MockBuilder();
        $this->className = 'AnotherClassName';

        $this->assertSame($this->subject, $this->subject->named($this->className));

        $definition = $this->subject->definition();

        $this->assertSame($this->className, $definition->className());
    }

    public function testNamedFailureInvalid()
    {
        $this->subject = new MockBuilder();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidClassNameException');
        $this->subject->named('1');
    }

    public function testNamedFailureFinalized()
    {
        $this->subject = new MockBuilder();
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->named('AnotherClassName');
    }

    public function testFinalize()
    {
        $this->subject = new MockBuilder();

        $this->assertFalse($this->subject->isFinalized());
        $this->assertSame($this->subject, $this->subject->finalize());
        $this->assertTrue($this->subject->isFinalized());
        $this->assertSame($this->subject, $this->subject->finalize());
        $this->assertTrue($this->subject->isFinalized());
    }

    public function testBuild()
    {
        $this->setUpWith($this->typeNames);
        $actual = $this->subject->build();

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('ReflectionClass', $actual);
        $this->assertTrue($actual->implementsInterface('Eloquent\Phony\Mock\MockInterface'));
        $this->assertTrue($actual->isSubclassOf('Eloquent\Phony\Test\TestClassB'));
        $this->assertSame($actual, $this->subject->build());
    }

    public function testBuildWithTraversableOnly()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceC');
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Traversable'));
        $this->assertTrue($actual->implementsInterface('IteratorAggregate'));
    }

    public function testBuildWithTraversableAndIterator()
    {
        $this->setUpWith(
            array('Iterator', 'Eloquent\Phony\Test\TestInterfaceC')
        );
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Traversable'));
        $this->assertTrue($actual->implementsInterface('Iterator'));
        $this->assertFalse($actual->implementsInterface('IteratorAggregate'));
    }

    public function testBuildWithTraversableAndIteratorAggregate()
    {
        $this->setUpWith(
            array('IteratorAggregate', 'Eloquent\Phony\Test\TestInterfaceC')
        );
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Traversable'));
        $this->assertTrue($actual->implementsInterface('IteratorAggregate'));
        $this->assertFalse($actual->implementsInterface('Iterator'));
    }

    public function testBuildFailureClassExists()
    {
        $builder = new MockBuilder(null, null, __CLASS__);
        $exception = null;
        try {
            $builder->build();
        } catch (ClassExistsException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertTrue($builder->isFinalized());
        $this->assertFalse($builder->isBuilt());
    }

    public function testClassName()
    {
        $this->setUpWith($this->typeNames);
        $actual = $this->subject->className();

        $this->assertRegExp('/^PhonyMock_TestClassB_\d+$/', $actual);
        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertSame($actual, $this->subject->className());
    }

    public function testGet()
    {
        $this->setUpWith($this->typeNames);
        $actual = $this->subject->get();

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertSame($actual, $this->subject->get());
    }

    public function testCreate()
    {
        $this->setUpWith($this->typeNames);
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
        $this->setUpWith($this->typeNames);
        $first = $this->subject->createWith(array('a', 'b'), 'label');

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertSame('label', $this->proxyFactory->createStubbing($first)->label());
        $this->assertSame(array('a', 'b'), $first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->createWith(array());

        $this->assertNotSame($first, $second);
        $this->assertSame('0', $this->proxyFactory->createStubbing($second)->label());
        $this->assertSame(array(), $second->constructorArguments);
        $this->assertSame($second, $this->subject->get());

        $third = $this->subject->createWith();

        $this->assertNotSame($first, $third);
        $this->assertNotSame($second, $third);
        $this->assertSame('1', $this->proxyFactory->createStubbing($third)->label());
        $this->assertNull($third->constructorArguments);
        $this->assertSame($third, $this->subject->get());
    }

    public function testFull()
    {
        $this->setUpWith($this->typeNames);
        $first = $this->subject->full('label');

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertSame('label', $this->proxyFactory->createStubbing($first)->label());
        $this->assertNull($first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->full();

        $this->assertNotSame($first, $second);
        $this->assertNull($second->constructorArguments);
        $this->assertSame($second, $this->subject->get());
    }

    public function testMockedConstructorWithReferenceParameters()
    {
        $first = null;
        $second = null;
        $builder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $builder->createWith(array(&$first, &$second));

        $this->assertSame('first', $first);
        $this->assertSame('second', $second);
    }
}
