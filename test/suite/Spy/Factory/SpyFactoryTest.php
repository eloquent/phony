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
        $this->subject = new SpyFactory($this->callFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callFactory, $this->subject->callFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyFactory();

        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
    }

    public function testCreate()
    {
        $subject = function () {};
        $expected = new Spy($subject, null, $this->callFactory);
        $actual = $this->subject->create($subject);

        $this->assertEquals($expected, $actual);
        $this->assertSame($subject, $actual->subject());
        $this->assertSame($this->callFactory, $actual->callFactory());
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
