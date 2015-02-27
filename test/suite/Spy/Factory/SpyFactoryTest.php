<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Spy;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->callFactory = new CallFactory();
        $this->generatorSpyFactory = new GeneratorSpyFactory();
        $this->traversableSpyFactory = new TraversableSpyFactory();
        $this->subject = new SpyFactory(
            $this->labelSequencer,
            $this->callFactory,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->labelSequencer, $this->subject->labelSequencer());
        $this->assertSame($this->callFactory, $this->subject->callFactory());
        $this->assertSame($this->generatorSpyFactory, $this->subject->generatorSpyFactory());
        $this->assertSame($this->traversableSpyFactory, $this->subject->traversableSpyFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyFactory();

        $this->assertSame(Sequencer::sequence('spy-label'), $this->subject->labelSequencer());
        $this->assertSame(CallFactory::instance(), $this->subject->callFactory());
        $this->assertSame(GeneratorSpyFactory::instance(), $this->subject->generatorSpyFactory());
        $this->assertSame(TraversableSpyFactory::instance(), $this->subject->traversableSpyFactory());
    }

    public function testCreate()
    {
        $callback = function () {};
        $useGeneratorSpies = false;
        $useTraversableSpies = false;
        $expected = new Spy(
            $callback,
            '0',
            $useGeneratorSpies,
            $useTraversableSpies,
            $this->callFactory,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        $actual = $this->subject->create($callback, $useTraversableSpies, $useGeneratorSpies);

        $this->assertEquals($expected, $actual);
        $this->assertSame($useGeneratorSpies, $actual->useGeneratorSpies());
        $this->assertSame($useTraversableSpies, $actual->useTraversableSpies());
        $this->assertSame($callback, $actual->callback());
        $this->assertSame($this->callFactory, $actual->callFactory());
        $this->assertSame($this->generatorSpyFactory, $actual->generatorSpyFactory());
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
