<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CallVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->iterableVerifierFactory = IterableVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->subject = new CallVerifierFactory(
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
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
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $adaptedCall = $this->subject->fromCall($this->callA);

        $this->assertEquals($verifier, $adaptedCall);
    }

    public function testFromCalls()
    {
        $calls = [$this->callA, $this->callB];
        $actual = $this->subject->fromCalls($calls);
        $expected = [
            new CallVerifier(
                $this->callA,
                $this->matcherFactory,
                $this->matcherVerifier,
                $this->generatorVerifierFactory,
                $this->iterableVerifierFactory,
                $this->assertionRecorder,
                $this->assertionRenderer
            ),
            new CallVerifier(
                $this->callB,
                $this->matcherFactory,
                $this->matcherVerifier,
                $this->generatorVerifierFactory,
                $this->iterableVerifierFactory,
                $this->assertionRecorder,
                $this->assertionRenderer
            ),
        ];

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
