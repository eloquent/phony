<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer\Builder;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class GeneratorAnswerBuilderFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->generatorAnswerBuilderFactory;
    }

    public function testCreate()
    {
        $stub = $this->container->stubFactory->create(null, null);
        $expected = new GeneratorAnswerBuilder(
            $stub,
            $this->container->invocableInspector,
            $this->container->invoker
        );
        $actual = $this->subject->create($stub);

        $this->assertEquals($expected, $actual);
    }
}
