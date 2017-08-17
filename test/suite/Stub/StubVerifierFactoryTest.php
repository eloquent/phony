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

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Hook\FunctionHookManager;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyFactory;
use Eloquent\Phony\Spy\IterableSpyFactory;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StubVerifierFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->spyFactory = new SpyFactory(
            new Sequencer(),
            $this->callFactory,
            Invoker::instance(),
            GeneratorSpyFactory::instance(),
            IterableSpyFactory::instance()
        );
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->featureDetector = FeatureDetector::instance();
        $this->stubFactory = new StubFactory(
            new Sequencer(),
            $this->matcherFactory,
            $this->matcherVerifier,
            Invoker::instance(),
            InvocableInspector::instance(),
            new EmptyValueFactory($this->featureDetector),
            GeneratorAnswerBuilderFactory::instance()
        );
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->iterableVerifierFactory = IterableVerifierFactory::instance();
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->functionHookManager = FunctionHookManager::instance();
        $this->subject = new StubVerifierFactory(
            $this->stubFactory,
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory,
            $this->functionHookManager
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
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
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
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
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
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory
        );
        $actual = $this->subject->createFromCallback($callback);

        $this->assertEquals($expected, $actual);
        $this->assertTrue($actual->useGeneratorSpies());
        $this->assertFalse($actual->useIterableSpies());
    }

    public function testCreateGlobal()
    {
        $actual = $this->subject->createGlobal('sprintf', 'Eloquent\Phony\Test\StubVerifierFactory');
        $actual->with('a', 'b')->returns('c');
        $actual->with('%s, %s, %s', 'a', 'b', 'c')->forwards();

        $this->assertSame('c', \Eloquent\Phony\Test\StubVerifierFactory\sprintf('a', 'b'));
        $this->assertSame('a, b, c', \Eloquent\Phony\Test\StubVerifierFactory\sprintf('%s, %s, %s', 'a', 'b', 'c'));
        $this->assertNull(\Eloquent\Phony\Test\StubVerifierFactory\sprintf('x', 'y'));
    }

    public function testCreateGlobalWithReferenceParameters()
    {
        $actual = $this->subject->createGlobal('preg_match', 'Eloquent\Phony\Test\SpyVerifierFactory');
        $actual->setsArgument(2, ['a', 'b']);

        \Eloquent\Phony\Test\SpyVerifierFactory\preg_match('/./', 'a', $matches);

        $this->assertSame(['a', 'b'], $matches);
    }

    public function testCreateGlobalFailureWithNonGlobal()
    {
        $this->expectException(
            'InvalidArgumentException',
            'Only functions in the global namespace are supported.'
        );
        $this->subject->createGlobal('Namespaced\\function', '\Eloquent\Phony\Test\StubVerifierFactory');
    }

    public function testCreateGlobalFailureEmptyNamespace()
    {
        $this->expectException('InvalidArgumentException', 'The supplied namespace must not be empty.');
        $this->subject->createGlobal('implode', '');
    }

    public function testCreateGlobalFailureGlobalNamespace()
    {
        $this->expectException('InvalidArgumentException', 'The supplied namespace must not be empty.');
        $this->subject->createGlobal('implode', '\\');
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
