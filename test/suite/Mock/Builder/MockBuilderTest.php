<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder;

use ArrayIterator;
use ArrayObject;
use Countable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Exception\FinalClassException;
use Eloquent\Phony\Mock\Exception\FinalizedMockException;
use Eloquent\Phony\Mock\Exception\InvalidClassNameException;
use Eloquent\Phony\Mock\Exception\InvalidDefinitionException;
use Eloquent\Phony\Mock\Exception\InvalidTypeException;
use Eloquent\Phony\Mock\Exception\MultipleInheritanceException;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestClassI;
use Eloquent\Phony\Test\TestFinalClass;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestInterfaceC;
use Eloquent\Phony\Test\TestInterfaceF;
use Eloquent\Phony\Test\TestInterfaceH;
use Eloquent\Phony\Test\TestTraitA;
use Eloquent\Phony\Test\TestTraitB;
use Error;
use Exception;
use Iterator;
use IteratorAggregate;
use Nonexistent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use Reflector;
use Serializable;
use Throwable;
use Traversable;

class MockBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = new FeatureDetector();

        $this->typeNames = [
            TestClassB::class,
            TestInterfaceA::class,
            Iterator::class,
            Countable::class,
        ];
        $this->typeNamesTraits = [
            TestClassB::class,
            TestInterfaceA::class,
            Iterator::class,
            Countable::class,
            TestTraitA::class,
            TestTraitB::class,
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

    protected function assertTypes(array $expectedTypes, array $expectedNonTypes, MockBuilder $actual)
    {
        $class = $actual->build();
        $actualRendered = implode('&', array_keys($actual->types()));

        foreach ($expectedTypes as $type) {
            if (interface_exists($type)) {
                $this->assertTrue(
                    $class->implementsInterface($type),
                    sprintf(
                        'Expected %s to implement interface %s.',
                        var_export($actualRendered, true),
                        var_export($type, true)
                    )
                );
            } else {
                $this->assertTrue(
                    $class->isSubclassOf($type),
                    sprintf(
                        'Expected %s to be a sub-class of %s.',
                        var_export($actualRendered, true),
                        var_export($type, true)
                    )
                );
            }
        }

        foreach ($expectedNonTypes as $type) {
            if (interface_exists($type)) {
                $this->assertFalse(
                    $class->implementsInterface($type),
                    sprintf(
                        'Expected %s to not implement %s.',
                        var_export($actualRendered, true),
                        var_export($type, true)
                    )
                );
            } else {
                $this->assertFalse(
                    $class->isSubclassOf($type),
                    sprintf(
                        'Expected %s to not be a sub-class of %s.',
                        var_export($actualRendered, true),
                        var_export($type, true)
                    )
                );
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
                TestClassB::class,
                TestInterfaceA::class,
                Iterator::class,
                Countable::class,
                TestClassB::class,
                TestInterfaceA::class,
                Iterator::class,
                Countable::class,
            ]
        );

        $this->assertEquals($this->typesFor($this->typeNames), $this->subject->types());
    }

    public function testConstructorWithTraits()
    {
        $this->setUpWith(
            [
                TestClassB::class,
                TestInterfaceA::class,
                Iterator::class,
                Countable::class,
                TestTraitA::class,
                TestTraitB::class,
                TestClassB::class,
                Iterator::class,
                Countable::class,
                TestTraitA::class,
                TestTraitB::class,
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
        $this->expectException(InvalidTypeException::class);
        $this->setUpWith([Nonexistent::class]);
    }

    public function testConstructorFailureFinalClass()
    {
        $this->expectException(FinalClassException::class);
        $this->setUpWith([TestFinalClass::class]);
    }

    public function testConstructorFailureMultipleInheritance()
    {
        $this->expectException(MultipleInheritanceException::class);
        $this->setUpWith([TestClassB::class, ArrayIterator::class]);
    }

    public function testConstructorFailureInvalidType()
    {
        $this->expectException(InvalidTypeException::class);
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
        $typeNames = [Iterator::class, Countable::class, Serializable::class];

        $this->assertSame($builder, $builder->like(Iterator::class, [Countable::class, Serializable::class]));
        $this->assertEquals($this->typesFor($typeNames), $builder->types());
    }

    public function testLikeFailureUndefinedClass()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidTypeException::class);
        $this->subject->like(Nonexistent::class);
    }

    public function testLikeFailureFinalClass()
    {
        $this->setUpWith([]);

        $this->expectException(FinalClassException::class);
        $this->subject->like(TestFinalClass::class);
    }

    public function testLikeFailureMultipleInheritance()
    {
        $this->setUpWith([]);

        $this->expectException(MultipleInheritanceException::class);
        $this->subject->like(TestClassB::class, ArrayIterator::class);
    }

    public function testLikeFailureMultipleInheritanceOnSubsequentCall()
    {
        $this->setUpWith(TestClassA::class);

        $this->expectException(MultipleInheritanceException::class);
        $this->subject->like(TestClassB::class, ArrayIterator::class);
    }

    public function testLikeFailureInvalidType()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidTypeException::class);
        $this->subject->like(1);
    }

    public function testLikeFailureInvalidObject()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidTypeException::class);
        $this->subject->like(new ArrayIterator());
    }

    public function testLikeFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException(FinalizedMockException::class);
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
            [
                'propertyA' => [null, 'valueA'],
                'propertyB' => [null, 'valueB'],
            ],
            $definition->customStaticProperties()
        );
        $this->assertSame(
            [
                'propertyC' => [null, 'valueC'],
                'propertyD' => [null, $this->callbackE],
            ],
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

        $this->expectException(InvalidDefinitionException::class);
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

        $this->expectException(FinalizedMockException::class);
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

        $this->expectException(FinalizedMockException::class);
        $this->subject->addStaticMethod('methodA', function () {});
    }

    public function testAddProperty()
    {
        $this->setUpWith([]);
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame([
            'propertyA' => [null, $value],
            'propertyB' => [null, null],
        ], $definition->customProperties());
    }

    public function testAddPropertyFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException(FinalizedMockException::class);
        $this->subject->addProperty('propertyA');
    }

    public function testAddStaticProperty()
    {
        $this->setUpWith([]);
        $value = 'value';

        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyA', $value));
        $this->assertSame($this->subject, $this->subject->addStaticProperty('propertyB'));

        $definition = $this->subject->definition();

        $this->assertSame([
            'propertyA' => [null, $value],
            'propertyB' => [null, null],
        ], $definition->customStaticProperties());
    }

    public function testAddStaticPropertyFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException(FinalizedMockException::class);
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

        $this->expectException(FinalizedMockException::class);
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

        $this->expectException(InvalidClassNameException::class);
        $this->subject->named('1');
    }

    public function testNamedFailureInvalidPostPhp71()
    {
        $this->setUpWith([]);

        $this->expectException(InvalidClassNameException::class);
        $this->subject->named("abc\x7fdef");
    }

    public function testNamedFailureFinalized()
    {
        $this->setUpWith([]);
        $this->subject->finalize();

        $this->expectException(FinalizedMockException::class);
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
        $this->assertInstanceOf(ReflectionClass::class, $actual);
        $this->assertTrue($actual->implementsInterface(Mock::class));
        $this->assertTrue($actual->isSubclassOf(TestClassB::class));
        $this->assertSame($actual, $this->subject->build());
    }

    public function buildIterablesData()
    {
        return [
            'Traversable' => [
                Traversable::class,
                [Traversable::class, Iterator::class],
                [IteratorAggregate::class],
            ],
            'Iterator' => [
                Iterator::class,
                [Traversable::class, Iterator::class],
                [IteratorAggregate::class],
            ],
            'IteratorAggregate' => [
                IteratorAggregate::class,
                [Traversable::class, IteratorAggregate::class],
                [Iterator::class],
            ],
            'Traversable + Iterator' => [
                [Traversable::class, Iterator::class],
                [Traversable::class, Iterator::class],
                [IteratorAggregate::class],
            ],
            'Traversable + IteratorAggregate' => [
                [Traversable::class, IteratorAggregate::class],
                [Traversable::class, IteratorAggregate::class],
                [Iterator::class],
            ],
            'Traversable child' => [
                TestInterfaceC::class,
                [Traversable::class, Iterator::class],
                [IteratorAggregate::class],
            ],
            'Traversable child + Iterator' => [
                [Iterator::class, TestInterfaceC::class],
                [Traversable::class, Iterator::class],
                [IteratorAggregate::class],
            ],
            'Traversable child + IteratorAggregate' => [
                [IteratorAggregate::class, TestInterfaceC::class],
                [Traversable::class, IteratorAggregate::class],
                [Iterator::class],
            ],
            'ArrayObject' => [
                ArrayObject::class,
                [Traversable::class, IteratorAggregate::class],
                [Iterator::class],
            ],
        ];
    }

    /**
     * @dataProvider buildIterablesData
     */
    public function testBuildIterables($typeNames, $expectedTypes, $expectedNonTypes)
    {
        $this->setUpWith($typeNames);

        $this->assertTypes($expectedTypes, $expectedNonTypes, $this->subject);
    }

    public function buildThrowablesData()
    {
        return [
            'Throwable' => [
                Throwable::class,
                [Throwable::class, Exception::class],
                [Error::class],
            ],
            'Exception' => [
                Exception::class,
                [Throwable::class, Exception::class],
                [Error::class],
            ],
            'Error' => [
                Error::class,
                [Throwable::class, Error::class],
                [Exception::class],
            ],
            'Throwable + Exception' => [
                [Throwable::class, Exception::class],
                [Throwable::class, Exception::class],
                [Error::class],
            ],
            'Throwable + Error' => [
                [Throwable::class, Error::class],
                [Throwable::class, Error::class],
                [Exception::class],
            ],
            'Throwable child' => [
                TestInterfaceF::class,
                [Throwable::class, Exception::class],
                [Error::class],
            ],
            'Throwable child + Exception' => [
                [Exception::class, TestInterfaceF::class],
                [Throwable::class, Exception::class],
                [Error::class],
            ],
            'Throwable child + Error' => [
                [Error::class, TestInterfaceF::class],
                [Throwable::class, Error::class],
                [Exception::class],
            ],
        ];
    }

    /**
     * @dataProvider buildThrowablesData
     */
    public function testBuildThrowables($typeNames, $expectedTypes, $expectedNonTypes)
    {
        $this->setUpWith($typeNames);

        $this->assertTypes($expectedTypes, $expectedNonTypes, $this->subject);
    }

    public function buildDateTimesData()
    {
        return [
            'DateTimeInterface' => [
                DateTimeInterface::class,
                [DateTimeInterface::class, DateTimeImmutable::class],
                [DateTime::class],
            ],
            'DateTimeImmutable' => [
                DateTimeImmutable::class,
                [DateTimeInterface::class, DateTimeImmutable::class],
                [DateTime::class],
            ],
            'DateTime' => [
                DateTime::class,
                [DateTimeInterface::class, DateTime::class],
                [DateTimeImmutable::class],
            ],
            'DateTimeInterface + DateTimeImmutable' => [
                [DateTimeInterface::class, DateTimeImmutable::class],
                [DateTimeInterface::class, DateTimeImmutable::class],
                [DateTime::class],
            ],
            'DateTimeInterface + DateTime' => [
                [DateTimeInterface::class, DateTime::class],
                [DateTimeInterface::class, DateTime::class],
                [DateTimeImmutable::class],
            ],
            'DateTimeInterface child' => [
                TestInterfaceH::class,
                [DateTimeInterface::class, DateTimeImmutable::class],
                [DateTime::class],
            ],
            'DateTimeInterface child + DateTimeImmutable' => [
                [DateTimeImmutable::class, TestInterfaceH::class],
                [DateTimeInterface::class, DateTimeImmutable::class],
                [DateTime::class],
            ],
            'DateTimeInterface child + DateTime' => [
                [DateTime::class, TestInterfaceH::class],
                [DateTimeInterface::class, DateTime::class],
                [DateTimeImmutable::class],
            ],
        ];
    }

    /**
     * @dataProvider buildDateTimesData
     */
    public function testBuildDateTimes($typeNames, $expectedTypes, $expectedNonTypes)
    {
        $this->setUpWith($typeNames);

        $this->assertTypes($expectedTypes, $expectedNonTypes, $this->subject);
    }

    public function testBuildWithReflectorInterface()
    {
        $this->setUpWith(Reflector::class);
        $actual = $this->subject->build();

        $this->assertTrue($actual->implementsInterface(Reflector::class));
    }

    public function testBuildWithFinalConstructor()
    {
        $this->setUpWith(TestClassI::class);
        $actual = $this->subject->build();

        $this->assertTrue($actual->isSubclassOf(TestClassI::class));
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
        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertInstanceOf(TestClassB::class, $actual);
        $this->assertSame($actual, $this->subject->get());
    }

    public function testPartial()
    {
        $this->setUpWith($this->typeNames);
        $first = $this->subject->partial('a', 'b');

        $this->assertTrue($this->subject->isFinalized());
        $this->assertTrue($this->subject->isBuilt());
        $this->assertInstanceOf(Mock::class, $first);
        $this->assertInstanceOf(TestClassB::class, $first);
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
        $this->assertInstanceOf(Mock::class, $first);
        $this->assertInstanceOf(TestClassB::class, $first);
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
        $this->assertInstanceOf(Mock::class, $first);
        $this->assertInstanceOf(TestClassB::class, $first);
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
        $builder = $this->setUpWith(TestClassA::class);
        $builder->partialWith([&$first, &$second]);

        $this->assertSame('first', $first);
        $this->assertSame('second', $second);
    }
}
