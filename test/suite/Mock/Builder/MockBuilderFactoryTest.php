<?php

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestInterfaceB;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MockBuilderFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->mockFactory = MockFactory::instance();
        $this->handleFactory = HandleFactory::instance();
        $this->invocableInspector = InvocableInspector::instance();
        $this->subject = new MockBuilderFactory(
            $this->mockFactory,
            $this->handleFactory,
            $this->invocableInspector
        );
    }

    public function testCreate()
    {
        $types = [TestInterfaceA::class, TestInterfaceB::class];
        $actual = $this->subject->create($types);
        $expected = new MockBuilder(
            $types,
            $this->mockFactory,
            $this->handleFactory,
            $this->invocableInspector
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->mockFactory, $actual->factory());
        $this->assertSame($this->handleFactory, $actual->handleFactory());
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
