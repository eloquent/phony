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
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockProxyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MockProxyFactory();
    }

    public function testCreateStatic()
    {
        $mockBuilder = new MockBuilder();
        $class = $mockBuilder->build();
        $expected = new StaticMockProxy($class);
        $actual = $this->subject->createStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreate()
    {
        $mockBuilder = new MockBuilder();
        $mock = $mockBuilder->create();
        $expected = new MockProxy($mock);
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
}
