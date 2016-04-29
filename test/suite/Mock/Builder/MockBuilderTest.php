<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder;

use ArrayIterator;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class MockBuilderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->invocableInspector = new InvocableInspector();
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

        $this->callbackReflectorA = new ReflectionFunction($this->callbackA);
        $this->callbackReflectorB = new ReflectionFunction($this->callbackB);
        $this->callbackReflectorC = new ReflectionFunction($this->callbackC);
        $this->callbackReflectorD = new ReflectionFunction($this->callbackD);
        $this->callbackReflectorE = new ReflectionFunction($this->callbackE);

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
    }

    protected function setUpWith($typeNames)
    {
        $this->factory = new MockFactory(new Sequencer(), MockGenerator::instance(), HandleFactory::instance());
        $this->handleFactory = HandleFactory::instance();

        return $this->subject = new MockBuilder(
            $typeNames,
            $this->factory,
            $this->handleFactory,
            $this->invocableInspector,
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
        $this->assertSame($this->handleFactory, $this->subject->handleFactory());
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
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
        $this->assertSame($this->handleFactory, $this->subject->handleFactory());
        $this->assertSame($this->featureDetector, $this->subject->featureDetector());
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorFailureUndefinedClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->setUpWith(array('Nonexistent'));
    }

    public function testConstructorFailureFinalClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalClassException');
        $this->setUpWith(array('Eloquent\Phony\Test\TestFinalClass'));
    }

    public function testConstructorFailureMultipleInheritance()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->setUpWith(array('Eloquent\Phony\Test\TestClassB', 'ArrayIterator'));
    }

    public function testConstructorFailureInvalidType()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->setUpWith(array(1));
    }

    public function testClone()
    {
        $builder = $this->setUpWith(array());
        $builder->addMethod('methodA');
        $mockA = $builder->get();
        $copy = clone $builder;
        $copy->addMethod('methodB');

        $this->assertTrue($builder->isFinalized());
        $this->assertTrue($builder->isBuilt());
        $this->assertFalse($copy->isFinalized());
        $this->assertFalse($copy->isBuilt());

        $mockB = $copy->get();

        $this->assertNotSame($mockA, $mockB);
        $this->assertFalse($mockA instanceof $mockB);
        $this->assertFalse($mockB instanceof $mockA);
        $this->assertTrue(method_exists($mockA, 'methodA'));
        $this->assertTrue(method_exists($mockB, 'methodA'));
        $this->assertTrue(method_exists($mockB, 'methodB'));
    }

    public function testLikeWithString()
    {
        $builder = $this->setUpWith(array());
        $typeNames = array('Iterator', 'Countable', 'Serializable');

        $this->assertSame($builder, $builder->like('Iterator', array('Countable', 'Serializable')));
        $this->assertEquals($this->typesFor($typeNames), $builder->types());
    }

    public function testLikeFailureUndefinedClass()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like('Nonexistent');
    }

    public function testLikeFailureFinalClass()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalClassException');
        $this->subject->like('Eloquent\Phony\Test\TestFinalClass');
    }

    public function testLikeFailureMultipleInheritance()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->subject->like('Eloquent\Phony\Test\TestClassB', 'ArrayIterator');
    }

    public function testLikeFailureMultipleInheritanceOnSubsequentCall()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->subject->like('Eloquent\Phony\Test\TestClassB', 'ArrayIterator');
    }

    public function testLikeFailureInvalidType()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like(1);
    }

    public function testLikeFailureInvalidObject()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like(new ArrayIterator());
    }

    public function testLikeFailureFinalized()
    {
        $this->setUpWith(array());
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->like('ClassName');
    }

    public function testLikeWithAdHocDefinitions()
    {
        $this->setUpWith(array());
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

        $this->assertSame($this->subject, $this->subject->like($this->definition));

        $definition = $this->subject->definition();

        $this->assertEquals(
            array(
                'methodA' => array($this->callbackA, $this->callbackReflectorA),
                'methodB' => array($this->callbackB, $this->callbackReflectorB),
            ),
            $definition->customStaticMethods()
        );
        $this->assertEquals(
            array(
                'methodC' => array($this->callbackC, $this->callbackReflectorC),
                'methodD' => array($this->callbackD, $this->callbackReflectorD),
            ),
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

    public function testLikeWithAdHocDefinitionsFailureInvalid()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidDefinitionException');
        $this->subject->like(array(1 => 'propertyA', 2 => 'valueA'));
    }

    public function testAddMethod()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith(array());
        $callback = function () {};
        $callbackReflector = new ReflectionFunction($callback);

        $this->assertSame($this->subject, $this->subject->addMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addMethod('methodB'));

        $definition = $this->subject->definition();

        $this->assertEquals(
            array('methodA' => array($callback, $callbackReflector), 'methodB' => array($callback, $callbackReflector)),
            $definition->customMethods()
        );
    }

    public function testAddMethodFailureFinalized()
    {
        $this->setUpWith(array());
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addMethod('methodA', function () {});
    }

    public function testAddStaticMethod()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM treats closures as inequal when created in different classes.');
        }

        $this->setUpWith(array());
        $callback = function () {};
        $callbackReflector = new ReflectionFunction($callback);

        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodB'));

        $definition = $this->subject->definition();

        $this->assertEquals(
            array('methodA' => array($callback, $callbackReflector), 'methodB' => array($callback, $callbackReflector)),
            $definition->customStaticMethods()
        );
    }

    public function testAddStaticMethodFailureFinalized()
    {
        $this->setUpWith(array());
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addStaticMethod('methodA', function () {});
    }

    public function testAddProperty()
    {
        $this->setUpWith(array());
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame(array('propertyA' => $value, 'propertyB' => null), $definition->customProperties());
    }

    public function testAddPropertyFailureFinalized()
    {
        $this->setUpWith(array());
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addProperty('propertyA');
    }

    public function testAddStaticProperty()
    {
        $this->setUpWith(array());
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame(array('propertyA' => $value, 'propertyB' => null), $definition->customStaticProperties());
    }

    public function testAddStaticPropertyFailureFinalized()
    {
        $this->setUpWith(array());
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addStaticProperty('propertyA');
    }

    public function testAddConstant()
    {
        $this->setUpWith(array());
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addConstant('CONSTANT_NAME', $value));

        $definition = $this->subject->definition();

        $this->assertSame(array('CONSTANT_NAME' => $value), $definition->customConstants());
    }

    public function testAddConstantFailureFinalized()
    {
        $this->setUpWith(array());
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addConstant('CONSTANT_NAME', 'value');
    }

    public function testNamed()
    {
        $this->setUpWith(array());
        $this->className = 'AnotherClassName';

        $this->assertSame($this->subject, $this->subject->named($this->className));

        $definition = $this->subject->definition();

        $this->assertSame($this->className, $definition->className());
    }

    public function testNamedFailureInvalid()
    {
        $this->setUpWith(array());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidClassNameException');
        $this->subject->named('1');
    }

    public function testNamedFailureFinalized()
    {
        $this->setUpWith(array());
        $this->subject->finalize();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->named('AnotherClassName');
    }

    public function testFinalize()
    {
        $this->setUpWith(array());

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
        $this->assertTrue($actual->implementsInterface('Eloquent\Phony\Mock\Mock'));
        $this->assertTrue($actual->isSubclassOf('Eloquent\Phony\Test\TestClassB'));
        $this->assertSame($actual, $this->subject->build());
    }

    public function testBuildWithTraversableOnly()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceC');
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Traversable'));
        $this->assertTrue($actual->implementsInterface('IteratorAggregate'));
        $this->assertFalse($actual->implementsInterface('Iterator'));
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

    public function testBuildWithTraversableAndIterator()
    {
        $this->setUpWith(
            array('Iterator', 'Eloquent\Phony\Test\TestInterfaceC')
        );
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Traversable'));
        $this->assertFalse($actual->implementsInterface('IteratorAggregate'));
        $this->assertTrue($actual->implementsInterface('Iterator'));
    }

    public function testBuildWithThrowableOnly()
    {
        if (!$this->featureDetector->isSupported('error.exception.engine')) {
            $this->markTestSkipped('Requires engine error exceptions.');
        }

        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceF');
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Throwable'));
        $this->assertTrue($actual->isSubclassOf('Exception'));
        $this->assertFalse($actual->isSubclassOf('Error'));
    }

    public function testBuildWithThrowableAndException()
    {
        if (!$this->featureDetector->isSupported('error.exception.engine')) {
            $this->markTestSkipped('Requires engine error exceptions.');
        }

        $this->setUpWith('Exception', 'Eloquent\Phony\Test\TestInterfaceF');
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Throwable'));
        $this->assertTrue($actual->isSubclassOf('Exception'));
        $this->assertFalse($actual->isSubclassOf('Error'));
    }

    public function testBuildWithThrowableAndError()
    {
        if (!$this->featureDetector->isSupported('error.exception.engine')) {
            $this->markTestSkipped('Requires engine error exceptions.');
        }

        $this->setUpWith('Error', 'Eloquent\Phony\Test\TestInterfaceF');
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Throwable'));
        $this->assertTrue($actual->isSubclassOf('Error'));
        $this->assertFalse($actual->isSubclassOf('Exception'));
    }

    public function testBuildWithFinalConstructor()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassI');
        $actual = $this->subject->build();

        $this->assertTrue($actual->isSubclassOf('Eloquent\Phony\Test\TestClassI'));
    }

    public function testBuildFailureClassExists()
    {
        $builder = $this->setUpWith(array());
        $builder->named(__CLASS__);
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
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertSame($actual, $this->subject->get());
    }

    public function testPartial()
    {
        $this->setUpWith($this->typeNames);
        $first = $this->subject->partial('a', 'b');

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertSame(array('a', 'b'), $first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->partial();

        $this->assertNotSame($first, $second);
        $this->assertSame(array(), $second->constructorArguments);
        $this->assertSame($second, $this->subject->get());
    }

    public function testPartialWith()
    {
        $this->setUpWith($this->typeNames);
        $first = $this->subject->partialWith(array('a', 'b'));

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertSame(array('a', 'b'), $first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->partialWith(array());

        $this->assertNotSame($first, $second);
        $this->assertSame(array(), $second->constructorArguments);
        $this->assertSame($second, $this->subject->get());

        $third = $this->subject->partialWith();

        $this->assertNotSame($first, $third);
        $this->assertNotSame($second, $third);
        $this->assertSame(array(), $second->constructorArguments);
        $this->assertSame($third, $this->subject->get());

        $third = $this->subject->partialWith(null);

        $this->assertNotSame($first, $third);
        $this->assertNotSame($second, $third);
        $this->assertNull($third->constructorArguments);
        $this->assertSame($third, $this->subject->get());
    }

    public function testFull()
    {
        $this->setUpWith($this->typeNames);
        $first = $this->subject->full();

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertNull($first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->full();

        $this->assertNotSame($first, $second);
        $this->assertNull($second->constructorArguments);
        $this->assertSame($second, $this->subject->get());
    }

    public function testSource()
    {
        $this->setUpWith(array());
        $this->subject->named('PhonyMockBuilderTestSourceMethod');
        $expected = <<<'EOD'
class PhonyMockBuilderTestSourceMethod
implements \Eloquent\Phony\Mock\Mock
{
    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}

EOD;

        $this->assertSame($expected, $this->subject->source());
        $this->assertTrue($this->subject->isFinalized());
    }

    public function testMockedConstructorWithReferenceParameters()
    {
        $first = null;
        $second = null;
        $builder = $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $builder->partialWith(array(&$first, &$second));

        $this->assertSame('first', $first);
        $this->assertSame('second', $second);
    }
}
