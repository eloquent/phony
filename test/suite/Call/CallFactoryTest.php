<?php

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\TestCallEventFactory;
use Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class CallFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->eventFactory = new TestCallEventFactory();
        $this->invoker = new Invoker();
        $this->subject = new CallFactory($this->eventFactory, $this->invoker);

        $this->spyFactory = SpyFactory::instance();
        $this->featureDetector = new FeatureDetector();
    }

    public function testRecord()
    {
        $callback = 'implode';
        $arguments = Arguments::create(['a', 'b']);
        $returnValue = 'ab';
        $spy = $this->spyFactory->create();
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $arguments));
        $expected->setResponseEvent($this->eventFactory->createReturned($returnValue));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
    }

    public function testRecordException()
    {
        $exception = new RuntimeException('You done goofed.');
        $callback = function () use ($exception) {
            throw $exception;
        };
        $arguments = Arguments::create(['a', 'b']);
        $spy = $this->spyFactory->create();
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $arguments));
        $expected->setResponseEvent($this->eventFactory->createThrew($exception));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
    }

    public function testRecordEngineErrorException()
    {
        $exception = new Error('You done goofed.');
        $callback = function () use ($exception) {
            throw $exception;
        };
        $arguments = Arguments::create(['a', 'b']);
        $spy = $this->spyFactory->create();
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $arguments));
        $expected->setResponseEvent($this->eventFactory->createThrew($exception));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
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
