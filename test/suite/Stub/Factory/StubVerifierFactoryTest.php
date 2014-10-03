<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Spy;
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
        $this->spyFactory = new SpyFactory($this->callFactory);
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->stubFactory = new StubFactory($this->matcherFactory, $this->matcherVerifier);
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->invoker = new Invoker();
        $this->subject = new StubVerifierFactory(
            $this->stubFactory,
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker
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
    }

    public function testCreate()
    {
        $stub = new Stub();
        $spy = new Spy();
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker
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
    }

    public function testCreateDefaults()
    {
        $stub = new Stub(null, null, $this->matcherFactory, $this->matcherVerifier);
        $spy = new Spy($stub, $this->callFactory);
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker
        );
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->invoker, $actual->invoker());
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $thisValue = (object) array();
        $stub = new Stub($callback, $thisValue, $this->matcherFactory, $this->matcherVerifier);
        $spy = new Spy($stub, $this->callFactory);
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker
        );
        $actual = $this->subject->createFromCallback($callback, $thisValue);

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $actual->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $actual->assertionRenderer());
        $this->assertSame($this->matcherFactory, $actual->stub()->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->stub()->matcherVerifier());
        $this->assertSame($this->invoker, $actual->invoker());
        $this->assertSame($this->callFactory, $actual->spy()->callFactory());
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
