<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Factory;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Generator\MockGenerator;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
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
        $this->generator = new MockGenerator();
        $this->proxyFactory = new ProxyFactory();
        $this->subject = new MockFactory($this->labelSequencer, $this->generator, $this->proxyFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->labelSequencer, $this->subject->labelSequencer());
        $this->assertSame($this->generator, $this->subject->generator());
        $this->assertSame($this->proxyFactory, $this->subject->proxyFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockFactory();

        $this->assertSame(Sequencer::sequence('mock-label'), $this->subject->labelSequencer());
        $this->assertSame(MockGenerator::instance(), $this->subject->generator());
        $this->assertSame(ProxyFactory::instance(), $this->subject->proxyFactory());
    }

    public function testCreateMockClass()
    {
        $builder = new MockBuilder(
            'Eloquent\Phony\Test\TestClassB',
            array(
                'static methodA' => function () {
                    return 'static custom ' . implode(func_get_args());
                },
                'methodB' => function () {
                    return 'custom ' . implode(func_get_args());
                },
            ),
            __NAMESPACE__ . '\PhonyMockFactoryTestCreateMockClass'
        );
        $actual = $this->subject->createMockClass($builder);
        $protectedMethod = $actual->getMethod('testClassAStaticMethodC');
        $protectedMethod->setAccessible(true);

        $this->assertInstanceOf('ReflectionClass', $actual);
        $this->assertTrue($actual->implementsInterface('Eloquent\Phony\Mock\MockInterface'));
        $this->assertTrue($actual->isSubclassOf('Eloquent\Phony\Test\TestClassB'));
        $this->assertSame($actual, $this->subject->createMockClass($builder));
        $this->assertSame('ab', PhonyMockFactoryTestCreateMockClass::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('protected ab', $protectedMethod->invoke(null, 'a', 'b'));
        $this->assertSame('static custom ab', PhonyMockFactoryTestCreateMockClass::methodA('a', 'b'));
    }

    public function testCreateMockClassFailureExists()
    {
        $builderA = new MockBuilder();
        $builderB = new MockBuilder(null, null, $builderA->build(true)->getName());

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\ClassExistsException');
        $this->subject->createMockClass($builderB);
    }

    public function testCreateMockClassFailureSyntax()
    {
        $this->subject = new MockFactory($this->labelSequencer, new TestMockGenerator('{'));
        $builder = new MockBuilder();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\MockGenerationFailedException');
        $this->subject->createMockClass($builder);
    }

    public function testCreateMock()
    {
        $builder = new MockBuilder(
            'Eloquent\Phony\Test\TestClassB',
            array(
                'static methodA' => function () {
                    return 'static custom ' . implode(func_get_args());
                },
                'methodB' => function () {
                    return 'custom ' . implode(func_get_args());
                },
            ),
            __NAMESPACE__ . '\PhonyMockFactoryTestCreateMock'
        );
        $actual = $this->subject->createMock($builder);
        $class = new ReflectionClass($actual);
        $protectedMethod = $class->getMethod('testClassAMethodC');
        $protectedMethod->setAccessible(true);
        $protectedStaticMethod = $class->getMethod('testClassAStaticMethodC');
        $protectedStaticMethod->setAccessible(true);

        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertSame('0', $this->proxyFactory->createStubbing($actual)->label());
        $this->assertSame('ab', $actual->testClassAMethodA('a', 'b'));
        $this->assertSame('protected ab', $protectedMethod->invoke($actual, 'a', 'b'));
        $this->assertSame('custom ab', $actual->methodB('a', 'b'));
        $this->assertSame('ab', PhonyMockFactoryTestCreateMock::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('protected ab', $protectedStaticMethod->invoke(null, 'a', 'b'));
        $this->assertSame('static custom ab', PhonyMockFactoryTestCreateMock::methodA('a', 'b'));
    }

    public function testCreateMockWithConstructorArgumentsAndId()
    {
        $builder = new MockBuilder(
            'Eloquent\Phony\Test\TestClassB',
            null,
            __NAMESPACE__ . '\PhonyMockFactoryTestCreateMockWithConstructorArguments'
        );
        $actual = $this->subject->createMock($builder, array('a', 'b'), 'label');
        $class = new ReflectionClass($actual);

        $this->assertSame(array('a', 'b'), $actual->constructorArguments);
        $this->assertSame('label', $this->proxyFactory->createStubbing($actual)->label());
    }

    public function testCreateMockWithConstructorArgumentsWithReferences()
    {
        $builder = new MockBuilder(
            'Eloquent\Phony\Test\TestClassA',
            null,
            __NAMESPACE__ . '\PhonyMockFactoryTestCreateMockWithConstructorArgumentsWithReferences'
        );
        $a = 'a';
        $b = 'b';
        $actual = $this->subject->createMock($builder, array(&$a, &$b));

        $this->assertSame(array('a', 'b'), $actual->constructorArguments);
        $this->assertSame('first', $a);
        $this->assertSame('second', $b);
    }

    public function testCreateMockWithOldConstructor()
    {
        $builder = new MockBuilder(
            'TestClassOldConstructor',
            null,
            __NAMESPACE__ . '\PhonyMockFactoryTestCreateMockWithOldConstructor'
        );
        $actual = $this->subject->createMock($builder, array('a', 'b'));

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
