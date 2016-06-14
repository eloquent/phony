<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Exception\MockGenerationFailedException;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestMockGenerator;
use Mockery\Generator\Generator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->generator = MockGenerator::instance();
        $this->handleFactory = HandleFactory::instance();
        $this->subject = new MockFactory($this->labelSequencer, $this->generator, $this->handleFactory);

        $this->builderFactory = MockBuilderFactory::instance();
        $this->featureDetector = FeatureDetector::instance();
    }

    public function testCreateMockClass()
    {
        $builder = $this->builderFactory->create(
            array(
                'Eloquent\Phony\Test\TestClassB',
                array(
                    'static methodA' => function () {
                        return 'static custom ' . implode(func_get_args());
                    },
                    'methodB' => function () {
                        return 'custom ' . implode(func_get_args());
                    },
                ),
            )
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

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\ClassExistsException');
        try {
            $this->subject->createMockClass($builderB->definition());
        } catch (ClassExistsException $e) {
            $this->assertSame($reporting, error_reporting());

            throw $e;
        }
    }

    public function testCreateMockClassFailureSyntax()
    {
        $this->subject = new MockFactory($this->labelSequencer, new TestMockGenerator('{'), $this->handleFactory);
        $builder = $this->builderFactory->create();
        $reporting = error_reporting();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MockGenerationFailedException');
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
            array(
                'Eloquent\Phony\Test\TestClassB',
                array(
                    'static methodA' => function () {
                        return 'static custom ' . implode(func_get_args());
                    },
                    'methodB' => function () {
                        return 'custom ' . implode(func_get_args());
                    },
                ),
            )
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
        $this->assertSame('0', $this->handleFactory->createStubbing($actual)->label());
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
            array(
                'Eloquent\Phony\Test\TestClassB',
                array(
                    'static methodA' => function () {
                        return 'static custom ' . implode(func_get_args());
                    },
                    'methodB' => function () {
                        return 'custom ' . implode(func_get_args());
                    },
                ),
            )
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
        $this->assertSame('0', $this->handleFactory->createStubbing($actual)->label());
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
        $actual = $this->subject->createPartialMock($builder->build(), array(&$a, &$b));

        $this->assertSame(array('a', 'b'), $actual->constructorArguments);
        $this->assertSame('first', $a);
        $this->assertSame('second', $b);
    }

    public function testCreatePartialMockWithOldConstructor()
    {
        if (!$this->featureDetector->isSupported('object.constructor.php4')) {
            $this->markTestSkipped('Requires PHP4-style constructors.');
        }

        require_once __DIR__ . '/../../src/TestClassOldConstructor.php';

        $builder = $this->builderFactory->create('TestClassOldConstructor');
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreatePartialMockWithOldConstructor');
        $actual = $this->subject->createPartialMock($builder->build(), array('a', 'b'));

        $this->assertSame(array('a', 'b'), $actual->constructorArguments);
    }

    public function testCreateFullMockWithFinalConstructor()
    {
        if (!method_exists('ReflectionClass', 'newInstanceWithoutConstructor')) {
            $this->markTestSkipped('Requires constructor bypassing.');
        }

        $builder = $this->builderFactory->create('Eloquent\Phony\Test\TestClassI');
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreateFullMockWithFinalConstructor');
        $actual = $this->subject->createFullMock($builder->build());

        $this->assertNull($actual->constructorArguments);
    }

    public function testCreatePartialMockWithFinalConstructor()
    {
        if (!method_exists('ReflectionClass', 'newInstanceWithoutConstructor')) {
            $this->markTestSkipped('Requires constructor bypassing.');
        }

        $builder = $this->builderFactory->create('Eloquent\Phony\Test\TestClassI');
        $builder->named(__NAMESPACE__ . '\PhonyMockFactoryTestCreatePartialMockWithFinalConstructor');
        $actual = $this->subject->createPartialMock($builder->build(), array('a', 'b'));

        $this->assertSame(array('a', 'b'), $actual->constructorArguments);
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
