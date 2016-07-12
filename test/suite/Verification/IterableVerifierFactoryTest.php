<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class IterableVerifierFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = MatcherFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->subject =
            new IterableVerifierFactory($this->matcherFactory, $this->assertionRecorder, $this->assertionRenderer);

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->subject->setCallVerifierFactory($this->callVerifierFactory);

        $this->spyFactory = SpyFactory::instance();
        $this->callFactory = new TestCallFactory();
        $this->eventFactory = $this->callFactory->eventFactory();
    }

    public function testCreate()
    {
        $spy = $this->spyFactory->create();
        $calls = array(
            $this->callFactory->create(),
            $this->callFactory->create(),
        );
        $expected = new IterableVerifier(
            $spy,
            $calls,
            $this->matcherFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->assertEquals($expected, $this->subject->create($spy, $calls));
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
