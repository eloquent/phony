<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class SpyFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->labelSequencer = new Sequencer();
        $this->callFactory = CallFactory::instance();
        $this->invoker = new Invoker();
        $this->generatorSpyFactory = GeneratorSpyFactory::instance();
        $this->iterableSpyFactory = IterableSpyFactory::instance();
        $this->subject = new SpyFactory(
            $this->labelSequencer,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
    }

    public function testCreate()
    {
        $callback = function () {};
        $expected = new SpyData(
            $callback,
            '0',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
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
