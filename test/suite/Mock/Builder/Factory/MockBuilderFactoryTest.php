<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Factory;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockBuilderFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockFactory = new MockFactory();
        $this->proxyFactory = new ProxyFactory();
        $this->subject = new MockBuilderFactory($this->mockFactory, $this->proxyFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->mockFactory, $this->subject->mockFactory());
        $this->assertSame($this->proxyFactory, $this->subject->proxyFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockBuilderFactory();

        $this->assertSame(MockFactory::instance(), $this->subject->mockFactory());
        $this->assertSame(ProxyFactory::instance(), $this->subject->proxyFactory());
    }

    public function testCreate()
    {
        $types = array('Eloquent\Phony\Test\TestInterfaceA', 'Eloquent\Phony\Test\TestInterfaceB');
        $definition = array('propertyA' => 'valueA', 'propertyB' =>'valueB');
        $className = 'PhonyMockMockBuilderFactoryTestCreate';
        $actual = $this->subject->create($types, $definition, $className);
        $expected = new MockBuilder($types, $definition, $className, $this->mockFactory);

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->mockFactory, $actual->factory());
        $this->assertSame($this->proxyFactory, $actual->proxyFactory());
    }

    public function testCreateMock()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = new Arguments(array('a', 'b'));
        $definition = array('propertyA' => 'valueA', 'propertyB' =>'valueB');
        $className = 'PhonyMockMockBuilderFactoryTestCreateMock';
        $actual = $this->subject->createMock($types, $arguments, $definition, $className);

        $this->assertInstanceOf($className, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertInstanceOf('Countable', $actual);
        $this->assertSame(array('a', 'b'), $actual->constructorArguments);
        $this->assertSame('ab', $actual->testClassAMethodA('a', 'b'));
    }

    public function testCreateMockWithNullArguments()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $arguments = null;
        $definition = array('propertyA' => 'valueA', 'propertyB' =>'valueB');
        $className = 'PhonyMockMockBuilderFactoryTestCreateMockWithNullArguments';
        $actual = $this->subject->createMock($types, $arguments, $definition, $className);

        $this->assertInstanceOf($className, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertInstanceOf('Countable', $actual);
        $this->assertNull($actual->constructorArguments);
        $this->assertSame('ab', $actual->testClassAMethodA('a', 'b'));
    }

    public function testCreateMockWithNoArguments()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $actual = $this->subject->createMock($types);

        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertInstanceOf('Countable', $actual);
        $this->assertEquals(array(), $actual->constructorArguments);
        $this->assertSame('ab', $actual->testClassAMethodA('a', 'b'));
    }

    public function testCreateMockDefaults()
    {
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $this->subject->createMock());
    }

    public function testCreateFullMock()
    {
        $types = array('Eloquent\Phony\Test\TestClassB', 'Countable');
        $definition = array('propertyA' => 'valueA', 'propertyB' =>'valueB');
        $className = 'PhonyMockMockBuilderFactoryTestCreateFullMock';
        $actual = $this->subject->createFullMock($types, $definition, $className);

        $this->assertInstanceOf($className, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertInstanceOf('Countable', $actual);
        $this->assertNull($actual->constructorArguments);
        $this->assertNull($actual->testClassAMethodA('a', 'b'));
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
