<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class SpyFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->spyFactory;
    }

    public function testCreate()
    {
        $callback = function () {};
        $expected = new SpyData(
            $callback,
            '0',
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );
        $actual = $this->subject->create($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->callback());
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
