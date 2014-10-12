<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Spy\Spy;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new CallFactory();
        $this->traversableSpyFactory = new TraversableSpyFactory();
        $this->subject = new SpyFactory($this->callFactory, $this->traversableSpyFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame($this->traversableSpyFactory, $this->subject->traversableSpyFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyFactory();

        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
        $this->assertSame(TraversableSpyFactory::instance(), $this->subject->traversableSpyFactory());
    }

    public function testCreate()
    {
        $callback = function () {};
        $useTraversableSpies = false;
        $useGeneratorSpies = false;
        $expected = new Spy(
            $callback,
            $useTraversableSpies,
            $useGeneratorSpies,
            $this->callFactory,
            $this->traversableSpyFactory
        );
        $actual = $this->subject->create($callback, $useTraversableSpies, $useGeneratorSpies);

        $this->assertEquals($expected, $actual);
        $this->assertSame($useTraversableSpies, $actual->useTraversableSpies());
        $this->assertSame($useGeneratorSpies, $actual->useGeneratorSpies());
        $this->assertSame($callback, $actual->callback());
        $this->assertSame($this->callFactory, $actual->callFactory());
        $this->assertSame($this->traversableSpyFactory, $actual->traversableSpyFactory());
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
