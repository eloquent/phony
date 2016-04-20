<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Spy;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->callFactory = CallFactory::instance();
        $this->invoker = new Invoker();
        $this->generatorSpyFactory = GeneratorSpyFactory::instance();
        $this->traversableSpyFactory = TraversableSpyFactory::instance();
        $this->subject = new SpyFactory(
            $this->labelSequencer,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
    }

    public function testCreate()
    {
        $callback = function () {};
        $expected = new Spy(
            $callback,
            '0',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        $actual = $this->subject->create($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->callback());
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
