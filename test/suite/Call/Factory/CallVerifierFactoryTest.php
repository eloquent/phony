<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\CallVerifier;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class CallVerifierFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = new MatcherFactory();
        $this->matcherFactory->addAvailableMatcherDrivers();
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new CallVerifierFactory(
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callFactory = new TestCallFactory();
        $this->callA = $this->callFactory->create();
        $this->callB = $this->callFactory->create();
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallVerifierFactory();

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
    }

    public function testAdapt()
    {
        $verifier = new CallVerifier($this->callA);
        $adaptedCall = $this->subject->adapt($this->callA);

        $this->assertSame($verifier, $this->subject->adapt($verifier));
        $this->assertNotSame($verifier, $adaptedCall);
        $this->assertEquals($verifier, $adaptedCall);
        $this->assertSame($this->matcherFactory, $adaptedCall->matcherFactory());
        $this->assertSame($this->matcherVerifier, $adaptedCall->matcherVerifier());
        $this->assertSame($this->assertionRecorder, $adaptedCall->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $adaptedCall->assertionRenderer());
        $this->assertSame($this->invocableInspector, $adaptedCall->invocableInspector());
    }

    public function testAdaptAll()
    {
        $callBVerifier = new CallVerifier($this->callB);
        $calls = array($this->callA, $callBVerifier);
        $actual = $this->subject->adaptAll($calls);
        $expected = array(new CallVerifier($this->callA), $callBVerifier);

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
