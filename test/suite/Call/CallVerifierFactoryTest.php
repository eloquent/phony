<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

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
}
