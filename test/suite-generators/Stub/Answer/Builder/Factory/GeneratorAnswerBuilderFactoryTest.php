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
use Eloquent\Phony\Stub\Factory\StubFactory;
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

    public function testCreate()
    {
        $stub = StubFactory::instance()->create();
        $expected = new GeneratorAnswerBuilder(
            $stub,
            $this->featureDetector->isSupported('generator.return'),
            $this->invocableInspector,
            $this->invoker
        );
        $actual = $this->subject->create($stub);

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
