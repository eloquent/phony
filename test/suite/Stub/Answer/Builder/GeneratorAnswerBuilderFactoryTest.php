<?php

namespace Eloquent\Phony\Stub\Answer\Builder;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Stub\StubFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GeneratorAnswerBuilderFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->invocableInspector = new InvocableInspector();
        $this->invoker = new Invoker();
        $this->subject = new GeneratorAnswerBuilderFactory($this->invocableInspector, $this->invoker);
    }

    public function testCreate()
    {
        $stub = StubFactory::instance()->create();
        $expected = new GeneratorAnswerBuilder(
            $stub,
            $this->invocableInspector,
            $this->invoker
        );
        $actual = $this->subject->create($stub);

        $this->assertEquals($expected, $actual);
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
