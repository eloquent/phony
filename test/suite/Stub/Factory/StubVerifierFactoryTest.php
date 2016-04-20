<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Factory\GeneratorSpyFactory;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactory;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Stub;
use Eloquent\Phony\Stub\StubVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class StubVerifierFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->spyFactory = new SpyFactory(
            new Sequencer(),
            $this->callFactory,
            Invoker::instance(),
            GeneratorSpyFactory::instance(),
            TraversableSpyFactory::instance()
        );
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->stubFactory = new StubFactory(
            new Sequencer(),
            $this->matcherFactory,
            $this->matcherVerifier,
            Invoker::instance(),
            InvocableInspector::instance(),
            GeneratorAnswerBuilderFactory::instance()
        );
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = AssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->invocableInspector = InvocableInspector::instance();
        $this->invoker = new Invoker();
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->subject = new StubVerifierFactory(
            $this->stubFactory,
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
    }

    public function testCreate()
    {
        $stub = $this->stubFactory->create();
        $spy = $this->spyFactory->create($stub)->setLabel('0');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->create($stub, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertSame($stub, $actual->stub());
        $this->assertSame($spy, $actual->spy());
    }

    public function testCreateDefaults()
    {
        $stub = $this->stubFactory->create()->setLabel('1');
        $spy = $this->spyFactory->create($stub)->setLabel('1');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $stub = $this->stubFactory->create($callback)->setLabel('1');
        $spy = $this->spyFactory->create($stub)->setLabel('1');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->createFromCallback($callback);

        $this->assertEquals($expected, $actual);
        $this->assertTrue($actual->useGeneratorSpies());
        $this->assertFalse($actual->useTraversableSpies());
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
