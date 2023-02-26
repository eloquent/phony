<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock;

use AllowDynamicProperties;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Exception\MockGenerationFailedException;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestClassI;
use Eloquent\Phony\Test\TestMockGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class MockFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->mockFactory;

        $this->builderFactory = $this->container->mockBuilderFactory;
        $this->handleFactory = $this->container->handleFactory;
    }

    public function testCreateMockClass()
    {
        $builder = $this->builderFactory->create(
            [
                TestClassB::class,
                [
                    'static methodA' => function () {
                        return 'static custom ' . implode(func_get_args());
                    },
                    'methodB' => function () {
                        return 'custom ' . implode(func_get_args());
                    },
                ],
            ]
        );
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreateMockClass');
        $actual = $this->subject->createMockClass($builder->definition());
        $protectedMethod = $actual->getMethod('testClassAStaticMethodC');
        $protectedMethod->setAccessible(true);

        $this->assertInstanceOf(ReflectionClass::class, $actual);
        $this->assertTrue($actual->implementsInterface(Mock::class));
        $this->assertTrue($actual->isSubclassOf(TestClassB::class));
        $this->assertSame($actual, $this->subject->createMockClass($builder->definition()));
        $this->assertSame('ab', PhonyMockFactoryTestCreateMockClass::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('protected ab', $protectedMethod->invoke(null, 'a', 'b'));
        $this->assertSame('static custom ab', PhonyMockFactoryTestCreateMockClass::methodA('a', 'b'));
    }

    public function testCreateMockClassFailureExists()
    {
        $builderA = $this->builderFactory->create();
        $builderB = $this->builderFactory->create();
        $builderB->named($builderA->className());
        $reporting = error_reporting();

        $this->expectException(ClassExistsException::class);
        try {
            $this->subject->createMockClass($builderB->definition());
        } catch (ClassExistsException $e) {
            $this->assertSame($reporting, error_reporting());

            throw $e;
        }
    }

    public function testCreateMockClassFailureSyntax()
    {
        $this->subject = new MockFactory(
            $this->container->mockLabelSequence,
            new TestMockGenerator(
                '{',
                $this->container->functionSignatureInspector,
                $this->container->featureDetector
            ),
            $this->container->mockRegistry,
            $this->handleFactory
        );
        $builder = $this->builderFactory->create();
        $reporting = error_reporting();

        $this->expectException(MockGenerationFailedException::class);
        try {
            $this->subject->createMockClass($builder->definition());
        } catch (MockGenerationFailedException $e) {
            $this->assertSame($reporting, error_reporting());

            throw $e;
        }
    }

    public function testCreateFullMock()
    {
        $builder = $this->builderFactory->create(
            [
                TestClassB::class,
                [
                    'static methodA' => function () {
                        return 'static custom ' . implode(func_get_args());
                    },
                    'methodB' => function () {
                        return 'custom ' . implode(func_get_args());
                    },
                ],
            ]
        );
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreateFullMock');
        $actual = $this->subject->createFullMock($builder->build());
        $class = new ReflectionClass($actual);
        $protectedMethod = $class->getMethod('testClassAMethodC');
        $protectedMethod->setAccessible(true);
        $protectedStaticMethod = $class->getMethod('testClassAStaticMethodC');
        $protectedStaticMethod->setAccessible(true);

        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertInstanceOf(TestClassB::class, $actual);
        $this->assertSame('0', $this->handleFactory->instanceHandle($actual)->label());
        $this->assertNull($actual->testClassAMethodA('a', 'b'));
        $this->assertNull($protectedMethod->invoke($actual, 'a', 'b'));
        $this->assertNull($actual->methodB('a', 'b'));
        $this->assertSame('ab', PhonyMockFactoryTestCreateFullMock::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('protected ab', $protectedStaticMethod->invoke(null, 'a', 'b'));
        $this->assertSame('static custom ab', PhonyMockFactoryTestCreateFullMock::methodA('a', 'b'));
    }

    public function testCreatePartialMock()
    {
        $builder = $this->builderFactory->create(
            [
                TestClassB::class,
                [
                    'static methodA' => function () {
                        return 'static custom ' . implode(func_get_args());
                    },
                    'methodB' => function () {
                        return 'custom ' . implode(func_get_args());
                    },
                ],
            ]
        );
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreatePartialMock');
        $actual = $this->subject->createPartialMock($builder->build());
        $class = new ReflectionClass($actual);
        $protectedMethod = $class->getMethod('testClassAMethodC');
        $protectedMethod->setAccessible(true);
        $protectedStaticMethod = $class->getMethod('testClassAStaticMethodC');
        $protectedStaticMethod->setAccessible(true);

        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertInstanceOf(TestClassB::class, $actual);
        $this->assertSame('0', $this->handleFactory->instanceHandle($actual)->label());
        $this->assertSame('ab', $actual->testClassAMethodA('a', 'b'));
        $this->assertSame('protected ab', $protectedMethod->invoke($actual, 'a', 'b'));
        $this->assertSame('custom ab', $actual->methodB('a', 'b'));
        $this->assertSame('ab', PhonyMockFactoryTestCreatePartialMock::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('protected ab', $protectedStaticMethod->invoke(null, 'a', 'b'));
        $this->assertSame('static custom ab', PhonyMockFactoryTestCreatePartialMock::methodA('a', 'b'));
    }

    public function testCreatePartialMockWithConstructorArgumentsWithReferences()
    {
        $builder = $this->builderFactory->create(TestClassA::class);
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreatePartialMockWithConstructorArgumentsWithReferences');
        $a = 'a';
        $b = 'b';
        $actual = $this->subject->createPartialMock($builder->build(), [&$a, &$b]);

        $this->assertSame(['a', 'b'], $actual->constructorArguments);
        $this->assertSame('first', $a);
        $this->assertSame('second', $b);
    }

    public function testCreateFullMockWithFinalConstructor()
    {
        $builder = $this->builderFactory->create(TestClassI::class);
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreateFullMockWithFinalConstructor');
        $actual = $this->subject->createFullMock($builder->build());

        $this->assertNull($actual->constructorArguments);
    }

    public function testCreatePartialMockWithFinalConstructor()
    {
        $builder = $this->builderFactory->create(TestClassI::class);
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreatePartialMockWithFinalConstructor');
        $actual = $this->subject->createPartialMock($builder->build(), ['a', 'b']);

        $this->assertSame(['a', 'b'], $actual->constructorArguments);
    }
}
