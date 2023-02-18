<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class GeneratorVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;

        $this->subject = $this->container->generatorVerifierFactory;
    }

    public function testCreate()
    {
        $spy = $this->container->spyFactory->create(null);
        $calls = [
            $this->callFactory->create(),
            $this->callFactory->create(),
        ];
        $expected = new GeneratorVerifier(
            $spy,
            $calls,
            $this->container->matcherFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
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
