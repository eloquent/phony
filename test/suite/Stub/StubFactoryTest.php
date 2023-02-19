<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class StubFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->stubFactory;
    }

    public function testCreate()
    {
        $callback = function () { return 'a'; };
        $defaultAnswerCallback = function ($stub) { $stub->forwards(); };
        $expected = new StubData(
            $callback,
            '0',
            $defaultAnswerCallback,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->invoker,
            $this->container->invocableInspector,
            $this->container->emptyValueFactory,
            $this->container->generatorAnswerBuilderFactory,
            $this->container->exporter,
            $this->container->assertionRenderer
        );
        $actual = $this->subject->create($callback, $defaultAnswerCallback);

        $this->assertEquals($expected, $actual);
        $this->assertSame('a', call_user_func($actual->callback()));
        $this->assertSame($actual, $actual->self());
    }
}
