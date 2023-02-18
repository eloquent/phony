<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class CallVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $callFactory = $this->container->callFactory;
        $this->subject = $this->container->callVerifierFactory;

        $this->callA = $callFactory->create();
        $this->callB = $callFactory->create();
    }

    public function testFromCall()
    {
        $verifier = new CallVerifier(
            $this->callA,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
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
                $this->container->matcherFactory,
                $this->container->matcherVerifier,
                $this->container->generatorVerifierFactory,
                $this->container->iterableVerifierFactory,
                $this->container->assertionRecorder,
                $this->container->assertionRenderer
            ),
            new CallVerifier(
                $this->callB,
                $this->container->matcherFactory,
                $this->container->matcherVerifier,
                $this->container->generatorVerifierFactory,
                $this->container->iterableVerifierFactory,
                $this->container->assertionRecorder,
                $this->container->assertionRenderer
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
