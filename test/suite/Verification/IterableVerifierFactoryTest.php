<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class IterableVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;

        $this->subject = $this->container->iterableVerifierFactory;
    }

    public function testCreate()
    {
        $spy = $this->container->spyFactory->create(null);
        $calls = [
            $this->callFactory->create(),
            $this->callFactory->create(),
        ];
        $expected = new IterableVerifier(
            $spy,
            $calls,
            $this->container->matcherFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );

        $this->assertEquals($expected, $this->subject->create($spy, $calls));
    }
}
