<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
            $this->container->exporter
        );
        $actual = $this->subject->create($callback, $defaultAnswerCallback);

        $this->assertEquals($expected, $actual);
        $this->assertSame('a', call_user_func($actual->callback()));
        $this->assertSame($actual, $actual->self());
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
