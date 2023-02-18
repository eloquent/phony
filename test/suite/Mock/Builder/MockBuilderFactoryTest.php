<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestInterfaceB;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class MockBuilderFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->mockBuilderFactory;
    }

    public function testCreate()
    {
        $types = [TestInterfaceA::class, TestInterfaceB::class];
        $actual = $this->subject->create($types);
        $expected = new MockBuilder(
            $types,
            $this->container->mockFactory,
            $this->container->handleFactory,
            $this->container->invocableInspector
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->container->mockFactory, $actual->factory());
        $this->assertSame($this->container->handleFactory, $actual->handleFactory());
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
