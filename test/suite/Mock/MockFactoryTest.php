<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Exception\MockGenerationFailedException;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestMockGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MockFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->generator = MockGenerator::instance();
        $this->handleFactory = HandleFactory::instance();
        $this->subject = new MockFactory($this->labelSequencer, $this->generator, $this->handleFactory);

        $this->builderFactory = MockBuilderFactory::instance();
    }

    public function testCreateMockClass()
    {
        $builder = $this->builderFactory->create(
            [
                'Eloquent\Phony\Test\TestClassB',
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

        $this->assertInstanceOf('ReflectionClass', $actual);
        $this->assertTrue($actual->implementsInterface('Eloquent\Phony\Mock\Mock'));
        $this->assertTrue($actual->isSubclassOf('Eloquent\Phony\Test\TestClassB'));
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

        $this->expectException('Eloquent\Phony\Mock\Exception\ClassExistsException');
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
            $this->labelSequencer,
            new TestMockGenerator('{'),
            $this->handleFactory
        );
        $builder = $this->builderFactory->create();
        $reporting = error_reporting();

        $this->expectException('Eloquent\Phony\Mock\Exception\MockGenerationFailedException');
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
                'Eloquent\Phony\Test\TestClassB',
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

        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
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
                'Eloquent\Phony\Test\TestClassB',
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

        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
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
        $builder = $this->builderFactory->create('Eloquent\Phony\Test\TestClassA');
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
        $builder = $this->builderFactory->create('Eloquent\Phony\Test\TestClassI');
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreateFullMockWithFinalConstructor');
        $actual = $this->subject->createFullMock($builder->build());

        $this->assertNull($actual->constructorArguments);
    }

    public function testCreatePartialMockWithFinalConstructor()
    {
        $builder = $this->builderFactory->create('Eloquent\Phony\Test\TestClassI');
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreatePartialMockWithFinalConstructor');
        $actual = $this->subject->createPartialMock($builder->build(), ['a', 'b']);

        $this->assertSame(['a', 'b'], $actual->constructorArguments);
    }

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
