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

use ArrayIterator;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;

class MockBuilderTest extends TestCase
{
    protected function setUp()
    {
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = new FeatureDetector();

        $this->typeNames = [
            'Eloquent\Phony\Test\TestClassB',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Iterator',
            'Countable',
        ];
        $this->typeNamesTraits = [
            'Eloquent\Phony\Test\TestClassB',
            'Eloquent\Phony\Test\TestInterfaceA',
            'Iterator',
            'Countable',
            'Eloquent\Phony\Test\TestTraitA',
            'Eloquent\Phony\Test\TestTraitB',
        ];

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

        $this->definition = [
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
        ];
    }

    protected function setUpWith($typeNames)
    {
        $this->handleFactory = HandleFactory::instance();
        $this->factory = new MockFactory(
            new Sequencer(),
            MockGenerator::instance(),
            $this->handleFactory,
            $this->featureDetector
        );

        return $this->subject = new MockBuilder(
            $typeNames,
            $this->factory,
            $this->handleFactory,
            $this->invocableInspector
        );
    }

    protected function typesFor($typeNames)
    {
        $types = [];

        foreach ($typeNames as $typeName) {
            $types[strtolower($typeName)] = new ReflectionClass($typeName);
        }

        return $types;
    }

    protected function assertTypes(array $expectedTypes, array $expectedNonTypes, ReflectionClass $actual)
    {
        foreach ($expectedTypes as $type) {
            if (interface_exists($type)) {
                $this->assertTrue($actual->implementsInterface($type));
            } else {
                $this->assertTrue($actual->isSubclassOf($type));
            }
        }

        foreach ($expectedNonTypes as $type) {
            if (interface_exists($type)) {
                $this->assertFalse($actual->implementsInterface($type));
            } else {
                $this->assertFalse($actual->isSubclassOf($type));
            }
        }
    }

    public function testConstructor()
    {
        $this->setUpWith($this->typeNames);

        $this->assertEquals($this->typesFor($this->typeNames), $this->subject->types());
        $this->assertSame($this->factory, $this->subject->factory());
        $this->assertSame($this->handleFactory, $this->subject->handleFactory());
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorWithDuplicateTypes()
    {
        $this->setUpWith(
            [
                'Eloquent\Phony\Test\TestClassB',
                'Eloquent\Phony\Test\TestInterfaceA',
                'Iterator',
                'Countable',
                'Eloquent\Phony\Test\TestClassB',
                'Eloquent\Phony\Test\TestInterfaceA',
                'Iterator',
                'Countable',
            ]
        );

        $this->assertEquals($this->typesFor($this->typeNames), $this->subject->types());
    }

    public function testConstructorWithTraits()
    {
        $this->setUpWith(
            [
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
            ]
        );

        $this->assertEquals($this->typesFor($this->typeNamesTraits), $this->subject->types());
        $this->assertSame($this->factory, $this->subject->factory());
        $this->assertSame($this->handleFactory, $this->subject->handleFactory());
        $this->assertFalse($this->subject->isFinalized());
        $this->assertFalse($this->subject->isBuilt());
    }

    public function testConstructorFailureUndefinedClass()
    {
        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->setUpWith(['Nonexistent']);
    }

    public function testConstructorFailureFinalClass()
    {
        $this->expectException('Eloquent\Phony\Mock\Exception\FinalClassException');
        $this->setUpWith(['Eloquent\Phony\Test\TestFinalClass']);
    }

    public function testConstructorFailureMultipleInheritance()
    {
        $this->expectException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->setUpWith(['Eloquent\Phony\Test\TestClassB', 'ArrayIterator']);
    }

    public function testConstructorFailureInvalidType()
    {
        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->setUpWith([1]);
    }

    public function testClone()
    {
        $builder = $this->setUpWith([]);
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
        $builder = $this->setUpWith([]);
        $typeNames = ['Iterator', 'Countable', 'Serializable'];

        $this->assertSame($builder, $builder->like('Iterator', ['Countable', 'Serializable']));
        $this->assertEquals($this->typesFor($typeNames), $builder->types());
    }

    public function testLikeFailureUndefinedClass()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like('Nonexistent');
    }

    public function testLikeFailureFinalClass()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalClassException');
        $this->subject->like('Eloquent\Phony\Test\TestFinalClass');
    }

    public function testLikeFailureMultipleInheritance()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->subject->like('Eloquent\Phony\Test\TestClassB', 'ArrayIterator');
    }

    public function testLikeFailureMultipleInheritanceOnSubsequentCall()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->expectException('Eloquent\Phony\Mock\Exception\MultipleInheritanceException');
        $this->subject->like('Eloquent\Phony\Test\TestClassB', 'ArrayIterator');
    }

    public function testLikeFailureInvalidType()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like(1);
    }

    public function testLikeFailureInvalidObject()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidTypeException');
        $this->subject->like(new ArrayIterator());
    }

    public function testLikeFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->like('ClassName');
    }

    public function testLikeWithAdHocDefinitions()
    {
        $this->setUpWith([]);
        $this->definition = [
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
        ];

        $this->assertSame($this->subject, $this->subject->like($this->definition));

        $definition = $this->subject->definition();

        $this->assertEquals(
            [
                'methodA' => [$this->callbackA, $this->callbackReflectorA],
                'methodB' => [$this->callbackB, $this->callbackReflectorB],
            ],
            $definition->customStaticMethods()
        );
        $this->assertEquals(
            [
                'methodC' => [$this->callbackC, $this->callbackReflectorC],
                'methodD' => [$this->callbackD, $this->callbackReflectorD],
            ],
            $definition->customMethods()
        );
        $this->assertSame(
            ['propertyA' => 'valueA', 'propertyB' => 'valueB'],
            $definition->customStaticProperties()
        );
        $this->assertSame(
            ['propertyC' => 'valueC', 'propertyD' => $this->callbackE],
            $definition->customProperties()
        );
        $this->assertSame(
            ['constantA' => 'constantValueA', 'constantB' => 'constantValueB'],
            $definition->customConstants()
        );
    }

    public function testLikeWithAdHocDefinitionsFailureInvalid()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidDefinitionException');
        $this->subject->like([1 => 'propertyA', 2 => 'valueA']);
    }

    public function testAddMethod()
    {
        $this->setUpWith([]);
        $callback = function () {};
        $callbackReflector = new ReflectionFunction($callback);

        $this->assertSame($this->subject, $this->subject->addMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addMethod('methodB'));

        $definition = $this->subject->definition();

        $this->assertEquals(
            ['methodA' => [$callback, $callbackReflector], 'methodB' => [$callback, $callbackReflector]],
            $definition->customMethods()
        );
    }

    public function testAddMethodFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addMethod('methodA', function () {});
    }

    public function testAddStaticMethod()
    {
        $this->setUpWith([]);
        $callback = function () {};
        $callbackReflector = new ReflectionFunction($callback);

        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodA', $callback));
        $this->assertSame($this->subject, $this->subject->addStaticMethod('methodB'));

        $definition = $this->subject->definition();

        $this->assertEquals(
            ['methodA' => [$callback, $callbackReflector], 'methodB' => [$callback, $callbackReflector]],
            $definition->customStaticMethods()
        );
    }

    public function testAddStaticMethodFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addStaticMethod('methodA', function () {});
    }

    public function testAddProperty()
    {
        $this->setUpWith([]);
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame(['propertyA' => $value, 'propertyB' => null], $definition->customProperties());
    }

    public function testAddPropertyFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addProperty('propertyA');
    }

    public function testAddStaticProperty()
    {
        $this->setUpWith([]);
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame(['propertyA' => $value, 'propertyB' => null], $definition->customStaticProperties());
    }

    public function testAddStaticPropertyFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addStaticProperty('propertyA');
    }

    public function testAddConstant()
    {
        $this->setUpWith([]);
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addConstant('CONSTANT_NAME', $value));

        $definition = $this->subject->definition();

        $this->assertSame(['CONSTANT_NAME' => $value], $definition->customConstants());
    }

    public function testAddConstantFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->addConstant('CONSTANT_NAME', 'value');
    }

    public function testNamed()
    {
        $this->setUpWith([]);
        $this->className = 'AnotherClassName';

        $this->assertSame($this->subject, $this->subject->named($this->className));

        $definition = $this->subject->definition();

        $this->assertSame($this->className, $definition->className());
    }

    public function testNamedFailureInvalid()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidClassNameException');
        $this->subject->named('1');
    }

    public function testNamedFailureInvalidPostPhp71()
    {
        $this->setUpWith([]);

        $this->expectException('Eloquent\Phony\Mock\Exception\InvalidClassNameException');
        $this->subject->named("abc\x7fdef");
    }

    public function testNamedFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException('Eloquent\Phony\Mock\Exception\FinalizedMockException');
        $this->subject->named('AnotherClassName');
    }

    public function testFinalize()
    {
        $this->setUpWith([]);

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

    public function buildIterablesData()
    {
        return [
            'Traversable' => [
                'Traversable',
                ['Traversable', 'Iterator'],
                ['IteratorAggregate'],
            ],
            'Iterator' => [
                'Iterator',
                ['Traversable', 'Iterator'],
                ['IteratorAggregate'],
            ],
            'IteratorAggregate' => [
                'IteratorAggregate',
                ['Traversable', 'IteratorAggregate'],
                ['Iterator'],
            ],
            'Traversable + Iterator' => [
                ['Traversable', 'Iterator'],
                ['Traversable', 'Iterator'],
                ['IteratorAggregate'],
            ],
            'Traversable + IteratorAggregate' => [
                ['Traversable', 'IteratorAggregate'],
                ['Traversable', 'IteratorAggregate'],
                ['Iterator'],
            ],
            'Traversable child' => [
                'Eloquent\Phony\Test\TestInterfaceC',
                ['Traversable', 'Iterator'],
                ['IteratorAggregate'],
            ],
            'Traversable child + Iterator' => [
                ['Iterator', 'Eloquent\Phony\Test\TestInterfaceC'],
                ['Traversable', 'Iterator'],
                ['IteratorAggregate'],
            ],
            'Traversable child + IteratorAggregate' => [
                ['IteratorAggregate', 'Eloquent\Phony\Test\TestInterfaceC'],
                ['Traversable', 'IteratorAggregate'],
                ['Iterator'],
            ],
            'ArrayObject' => [
                'ArrayObject',
                ['Traversable', 'IteratorAggregate'],
                ['Iterator'],
            ],
        ];
    }

    /**
     * @dataProvider buildIterablesData
     */
    public function testBuildIterables($typeNames, $expectedTypes, $expectedNonTypes)
    {
        $this->setUpWith($typeNames);

        $this->assertTypes($expectedTypes, $expectedNonTypes, $this->subject->build());
    }

    public function buildThrowablesData()
    {
        return [
            'Throwable' => [
                'Throwable',
                ['Throwable', 'Exception'],
                ['Error'],
            ],
            'Exception' => [
                'Exception',
                ['Throwable', 'Exception'],
                ['Error'],
            ],
            'Error' => [
                'Error',
                ['Throwable', 'Error'],
                ['Exception'],
            ],
            'Throwable + Exception' => [
                ['Throwable', 'Exception'],
                ['Throwable', 'Exception'],
                ['Error'],
            ],
            'Throwable + Error' => [
                ['Throwable', 'Error'],
                ['Throwable', 'Error'],
                ['Exception'],
            ],
            'Throwable child' => [
                'Eloquent\Phony\Test\TestInterfaceF',
                ['Throwable', 'Exception'],
                ['Error'],
            ],
            'Throwable child + Exception' => [
                ['Exception', 'Eloquent\Phony\Test\TestInterfaceF'],
                ['Throwable', 'Exception'],
                ['Error'],
            ],
            'Throwable child + Error' => [
                ['Error', 'Eloquent\Phony\Test\TestInterfaceF'],
                ['Throwable', 'Error'],
                ['Exception'],
            ],
        ];
    }

    /**
     * @dataProvider buildThrowablesData
     */
    public function testBuildThrowables($typeNames, $expectedTypes, $expectedNonTypes)
    {
        $this->setUpWith($typeNames);

        $this->assertTypes($expectedTypes, $expectedNonTypes, $this->subject->build());
    }

    public function buildDateTimesData()
    {
        return [
            'DateTimeInterface' => [
                'DateTimeInterface',
                ['DateTimeInterface', 'DateTimeImmutable'],
                ['DateTime'],
            ],
            'DateTimeImmutable' => [
                'DateTimeImmutable',
                ['DateTimeInterface', 'DateTimeImmutable'],
                ['DateTime'],
            ],
            'DateTime' => [
                'DateTime',
                ['DateTimeInterface', 'DateTime'],
                ['DateTimeImmutable'],
            ],
            'DateTimeInterface + DateTimeImmutable' => [
                ['DateTimeInterface', 'DateTimeImmutable'],
                ['DateTimeInterface', 'DateTimeImmutable'],
                ['DateTime'],
            ],
            'DateTimeInterface + DateTime' => [
                ['DateTimeInterface', 'DateTime'],
                ['DateTimeInterface', 'DateTime'],
                ['DateTimeImmutable'],
            ],
            'DateTimeInterface child' => [
                'Eloquent\Phony\Test\TestInterfaceH',
                ['DateTimeInterface', 'DateTimeImmutable'],
                ['DateTime'],
            ],
            'DateTimeInterface child + DateTimeImmutable' => [
                ['DateTimeImmutable', 'Eloquent\Phony\Test\TestInterfaceH'],
                ['DateTimeInterface', 'DateTimeImmutable'],
                ['DateTime'],
            ],
            'DateTimeInterface child + DateTime' => [
                ['DateTime', 'Eloquent\Phony\Test\TestInterfaceH'],
                ['DateTimeInterface', 'DateTime'],
                ['DateTimeImmutable'],
            ],
        ];
    }

    /**
     * @dataProvider buildDateTimesData
     */
    public function testBuildDateTimes($typeNames, $expectedTypes, $expectedNonTypes)
    {
        $this->setUpWith($typeNames);

        $this->assertTypes($expectedTypes, $expectedNonTypes, $this->subject->build());
    }

    public function testBuildWithReflectorInterface()
    {
        $this->setUpWith('Reflector');
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface('Reflector'));
    }

    public function testBuildWithFinalConstructor()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassI');
        $actual = $this->subject->build();

        $this->assertTrue($actual->isSubclassOf('Eloquent\Phony\Test\TestClassI'));
    }

    public function testBuildFailureClassExists()
    {
        $builder = $this->setUpWith([]);
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
        $this->assertSame(['a', 'b'], $first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->partial();

        $this->assertNotSame($first, $second);
        $this->assertSame([], $second->constructorArguments);
        $this->assertSame($second, $this->subject->get());
    }

    public function testPartialWith()
    {
        $this->setUpWith($this->typeNames);
        $first = $this->subject->partialWith(['a', 'b']);

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $first);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $first);
        $this->assertSame(['a', 'b'], $first->constructorArguments);
        $this->assertSame($first, $this->subject->get());

        $second = $this->subject->partialWith([]);

        $this->assertNotSame($first, $second);
        $this->assertSame([], $second->constructorArguments);
        $this->assertSame($second, $this->subject->get());

        $third = $this->subject->partialWith();

        $this->assertNotSame($first, $third);
        $this->assertNotSame($second, $third);
        $this->assertSame([], $second->constructorArguments);
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
        $this->setUpWith([]);
        $this->subject->named('PhonyMockBuilderTestSourceMethod');
        $expected = <<<'EOD'
class PhonyMockBuilderTestSourceMethod
implements \Eloquent\Phony\Mock\Mock
{
    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}

EOD;
        $expected = str_replace("\n", PHP_EOL, $expected);

        $this->assertSame($expected, $this->subject->source());
        $this->assertTrue($this->subject->isFinalized());
    }

    public function testMockedConstructorWithReferenceParameters()
    {
        $first = null;
        $second = null;
        $builder = $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $builder->partialWith([&$first, &$second]);

        $this->assertSame('first', $first);
        $this->assertSame('second', $second);
    }
}
