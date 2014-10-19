<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Factory;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Proxy\MockProxy;
use Eloquent\Phony\Mock\Proxy\StaticMockProxy;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionProperty;

class MockProxyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->subject = new MockProxyFactory($this->stubVerifierFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockProxyFactory();

        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
    }

    public function testCreateStatic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $property = new ReflectionProperty($class->getName(), '_staticStubs');
        $property->setAccessible(true);
        $expected = new StaticMockProxy($class->getName(), $this->expectedStubs($property->getValue(null)));
        $actual = $this->subject->createStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStaticWithObject()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $property = new ReflectionProperty($class->getName(), '_staticStubs');
        $property->setAccessible(true);
        $expected = new StaticMockProxy($class->getName(), $this->expectedStubs($property->getValue(null)));
        $actual = $this->subject->createStatic($mockBuilder->get());

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStaticWithString()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $property = new ReflectionProperty($class->getName(), '_staticStubs');
        $property->setAccessible(true);
        $expected = new StaticMockProxy($class->getName(), $this->expectedStubs($property->getValue(null)));
        $actual = $this->subject->createStatic($class->getName());

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStaticFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStatic('Nonexistent');
    }

    public function testCreateStaticFailureNonMockClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStatic(__CLASS__);
    }

    public function testCreate()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $property = new ReflectionProperty($mock, '_stubs');
        $property->setAccessible(true);
        $expected = new MockProxy($mock, $this->expectedStubs($property->getValue($mock)));
        $actual = $this->subject->create($mock);

        $this->assertEquals($expected, $actual);
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

    protected function expectedStubs(array $stubs)
    {
        foreach ($stubs as $name => $stub) {
            $stubs[$name] = $this->stubVerifierFactory->create($stub->callback(), $stub);
        }

        return $stubs;
    }
}
