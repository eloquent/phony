<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestInterfaceB;
use PHPUnit\Framework\TestCase;

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
            $this->container->mockGenerator,
            $this->container->mockFactory,
            $this->container->handleFactory,
            $this->container->invocableInspector,
            $this->container->featureDetector
        );

        $this->assertEquals($expected, $actual);
        $this->assertSame($this->container->mockFactory, $actual->factory());
        $this->assertSame($this->container->handleFactory, $actual->handleFactory());
    }
}
