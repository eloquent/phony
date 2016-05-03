<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\TraversableVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SpyVerifierFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), false);

        $this->spyFactory = new SpyFactory(
            new Sequencer(),
            CallFactory::instance(),
            Invoker::instance(),
            GeneratorSpyFactory::instance(),
            TraversableSpyFactory::instance()
        );
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->traversableVerifierFactory = TraversableVerifierFactory::instance();
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new SpyVerifierFactory(
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    protected function tearDown()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), true);
    }

    public function testCreate()
    {
        $spy = $this->spyFactory->create()->setLabel('0');
        $expected = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $actual = $this->subject->create($spy);

        $this->assertEquals($expected, $actual);
        $this->assertSame($spy, $actual->spy());
    }

    public function testCreateDefaults()
    {
        $spy = $this->spyFactory->create()->setLabel('1');
        $expected = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $spy = $this->spyFactory->create($callback)->setLabel('1');
        $expected = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $actual = $this->subject->createFromCallback($callback);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
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
