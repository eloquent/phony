<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer\Builder\Factory;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilder;
use Eloquent\Phony\Stub\Stub;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class GeneratorAnswerBuilderFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->invocableInspector = new InvocableInspector();
        $this->invoker = new Invoker();
        $this->featureDetector = new FeatureDetector();
        $this->subject = new GeneratorAnswerBuilderFactory(
            $this->invocableInspector,
            $this->invoker,
            $this->featureDetector
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
        $this->assertSame($this->invoker, $this->subject->invoker());
        $this->assertSame($this->featureDetector, $this->subject->featureDetector());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new GeneratorAnswerBuilderFactory();

        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
        $this->assertSame(FeatureDetector::instance(), $this->subject->featureDetector());
    }

    public function testCreate()
    {
        $stub = new Stub();
        $values = array('a', 'b');
        $expected = new GeneratorAnswerBuilder(
            $stub,
            $values,
            $this->featureDetector->isSupported('generator.return'),
            $this->invocableInspector,
            $this->invoker
        );
        $actual = $this->subject->create($stub, $values);

        $this->assertEquals($expected, $actual);
        $this->assertSame($stub, $actual->stub());
        $this->assertSame($this->invocableInspector, $actual->invocableInspector());
        $this->assertSame($this->invoker, $actual->invoker());
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
