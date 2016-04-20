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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyVerifier;
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
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = AssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new SpyVerifierFactory(
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    public function testCreate()
    {
        $spy = $this->spyFactory->create()->setLabel('0');
        $expected = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
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
