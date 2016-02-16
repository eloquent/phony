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
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Factory\SpyFactory;
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
        $this->spyFactory = new SpyFactory(new Sequencer(), null, $this->callFactory);
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->stubFactory = new StubFactory(new Sequencer(), $this->matcherFactory, $this->matcherVerifier);
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->invoker = new Invoker();
        $this->generatorAnswerBuilderFactory = new GeneratorAnswerBuilderFactory();
        $this->subject = new StubVerifierFactory(
            $this->stubFactory,
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->stubFactory, $this->subject->stubFactory());
        $this->assertSame($this->spyFactory, $this->subject->spyFactory());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $this->subject->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
        $this->assertSame($this->invoker, $this->subject->invoker());
        $this->assertSame($this->generatorAnswerBuilderFactory, $this->subject->generatorAnswerBuilderFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new StubVerifierFactory();

        $this->assertSame(StubFactory::instance(), $this->subject->stubFactory());
        $this->assertSame(SpyFactory::instance(), $this->subject->spyFactory());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(CallVerifierFactory::instance(), $this->subject->callVerifierFactory());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
        $this->assertSame(GeneratorAnswerBuilderFactory::instance(), $this->subject->generatorAnswerBuilderFactory());
    }

    public function testCreate()
    {
        $stub = new Stub(null, null, '0');
        $spy = new Spy(null, '0');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->create($stub, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertSame($stub, $actual->stub());
        $this->assertSame($spy, $actual->spy());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->invoker, $actual->invoker());
        $this->assertSame($this->generatorAnswerBuilderFactory, $actual->generatorAnswerBuilderFactory());
    }

    public function testCreateDefaults()
    {
        $stub = new Stub(null, null, '0', null, $this->matcherFactory, $this->matcherVerifier);
        $spy = new Spy($stub, '0', null, $this->callFactory);
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->invoker, $actual->invoker());
        $this->assertSame($this->generatorAnswerBuilderFactory, $actual->generatorAnswerBuilderFactory());
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $stub = new Stub(
            $callback,
            null,
            '0',
            null,
            $this->matcherFactory,
            $this->matcherVerifier
        );
        $spy = new Spy($stub, '0', null, $this->callFactory);
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->createFromCallback($callback);

        $this->assertEquals($expected, $actual);
        $this->assertTrue($actual->useGeneratorSpies());
        $this->assertFalse($actual->useTraversableSpies());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->matcherFactory, $actual->stub()->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->stub()->matcherVerifier());
        $this->assertSame($this->invoker, $actual->invoker());
        $this->assertSame($this->callFactory, $actual->spy()->callFactory());
        $this->assertSame($this->generatorAnswerBuilderFactory, $actual->generatorAnswerBuilderFactory());
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
