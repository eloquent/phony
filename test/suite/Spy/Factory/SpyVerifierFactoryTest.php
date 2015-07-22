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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
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

        $this->spyFactory = new SpyFactory(new Sequencer());
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
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

    public function testConstructor()
    {
        $this->assertSame($this->spyFactory, $this->subject->spyFactory());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $this->subject->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyVerifierFactory();

        $this->assertSame(SpyFactory::instance(), $this->subject->spyFactory());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(CallVerifierFactory::instance(), $this->subject->callVerifierFactory());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
    }

    public function testCreate()
    {
        $spy = new Spy(null, '0');
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->create($spy);

        $this->assertEquals($expected, $actual);
        $this->assertSame($spy, $actual->spy());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->invocableInspector, $actual->invocableInspector());
    }

    public function testCreateDefaults()
    {
        $spy = new Spy(null, '0');
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->invocableInspector, $actual->invocableInspector());
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $spy = new Spy($callback, '0', false, true);
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->createFromCallback($callback, false, true);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
        $this->assertFalse($actual->useGeneratorSpies());
        $this->assertTrue($actual->useTraversableSpies());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->invocableInspector, $actual->invocableInspector());
    }

    public function testCreateFromCallbackDefaults()
    {
        $spy = new Spy(null, '0');
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->createFromCallback();

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
        $this->assertTrue($actual->useGeneratorSpies());
        $this->assertFalse($actual->useTraversableSpies());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->invocableInspector, $actual->invocableInspector());
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
