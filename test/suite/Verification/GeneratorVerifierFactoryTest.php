<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use AllowDynamicProperties;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class GeneratorVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->matcherFactory = MatcherFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->subject =
            new GeneratorVerifierFactory($this->matcherFactory, $this->assertionRecorder, $this->assertionRenderer);

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->subject->setCallVerifierFactory($this->callVerifierFactory);

        $this->spyFactory = SpyFactory::instance();
        $this->callFactory = new TestCallFactory();
        $this->eventFactory = $this->callFactory->eventFactory();
    }

    public function testCreate()
    {
        $spy = $this->spyFactory->create(null);
        $calls = [
            $this->callFactory->create(),
            $this->callFactory->create(),
        ];
        $expected = new GeneratorVerifier(
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
