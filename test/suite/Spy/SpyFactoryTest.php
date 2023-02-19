<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

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
}
