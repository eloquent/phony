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
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Spy;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new CallFactory();
        $this->sequencer = new Sequencer();
        $this->clock = new SystemClock();
        $this->subject = new SpyFactory($this->callFactory, $this->sequencer, $this->clock);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame($this->sequencer, $this->subject->sequencer());
        $this->assertSame($this->clock, $this->subject->clock());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyFactory();

        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
        $this->assertEquals($this->sequencer, $this->subject->sequencer());
        $this->assertSame(SystemClock::instance(), $this->subject->clock());
    }

    public function testCreate()
    {
        $subject = function () {};
        $expected = new Spy($subject, $this->callFactory, $this->sequencer, $this->clock);
        $actual = $this->subject->create($subject);

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->callFactory, $actual->callFactory());
        $this->assertSame($this->sequencer, $actual->sequencer());
        $this->assertSame($this->clock, $actual->clock());
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
