<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class CallVerifierFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherFactory->addDefaultMatcherDrivers();
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
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

    public function testFromCall()
    {
        $verifier = new CallVerifier(
            $this->callA,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $adaptedCall = $this->subject->fromCall($this->callA);

        $this->assertEquals($verifier, $adaptedCall);
    }

    public function testFromCalls()
    {
        $calls = array($this->callA, $this->callB);
        $actual = $this->subject->fromCalls($calls);
        $expected = array(
            new CallVerifier(
                $this->callA,
                $this->matcherFactory,
                $this->matcherVerifier,
                $this->assertionRecorder,
                $this->assertionRenderer,
                $this->invocableInspector
            ),
            new CallVerifier(
                $this->callB,
                $this->matcherFactory,
                $this->matcherVerifier,
                $this->assertionRecorder,
                $this->assertionRenderer,
                $this->invocableInspector
            ),
        );

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
