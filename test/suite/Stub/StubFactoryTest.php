<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class StubFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = FeatureDetector::instance();
        $this->emptyValueFactory = new EmptyValueFactory($this->featureDetector);
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->subject = new StubFactory(
            $this->labelSequencer,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );
    }

    public function testCreate()
    {
        $callback = function () { return 'a'; };
        $self = (object) array();
        $defaultAnswerCallback = function ($stub) { $stub->forwards(); };
        $expected = new StubData(
            $callback,
            $self,
            0,
            $defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->create($callback, $self, $defaultAnswerCallback);

        $this->assertEquals($expected, $actual);
        $this->assertSame('a', call_user_func($actual->callback()));
        $this->assertSame($self, $actual->self());
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
