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

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockBuilderFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->idSequencer = new Sequencer();
        $this->mockFactory = new MockFactory();
        $this->subject = new MockBuilderFactory($this->idSequencer, $this->mockFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->idSequencer, $this->subject->idSequencer());
        $this->assertSame($this->mockFactory, $this->subject->mockFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockBuilderFactory();

        $this->assertSame(Sequencer::sequence('mock-builder-id'), $this->subject->idSequencer());
        $this->assertSame(MockFactory::instance(), $this->subject->mockFactory());
    }

    public function testCreate()
    {
        $types = array('Eloquent\Phony\Test\TestInterfaceA', 'Eloquent\Phony\Test\TestInterfaceB');
        $definition = array('propertyA' => 'valueA', 'propertyB' =>'valueB');
        $className = 'PhonyMockMockBuilderFactoryTestCreate';
        $actual = $this->subject->create($types, $definition, $className);
        $expected = new MockBuilder($types, $definition, $className, 0, $this->mockFactory);

        $this->assertEquals($expected, $actual);
        $this->assertSame('0', $actual->id());
        $this->assertSame($this->mockFactory, $actual->factory());
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
