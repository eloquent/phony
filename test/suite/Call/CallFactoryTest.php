<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestCallEventFactory;
use Error;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use RuntimeException;

#[AllowDynamicProperties]
class CallFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->eventFactory = new TestCallEventFactory();
        $container = new FacadeContainer(
            eventFactory: $this->eventFactory,
        );
        $this->subject = $container->callFactory;
        $this->spyFactory = $container->spyFactory;
    }

    public function testRecord()
    {
        $callback = 'implode';
        $parameters = (new ReflectionFunction('implode'))->getParameters();
        $parameterNames = ['separator', 'array'];
        $arguments = Arguments::create(['a', 'b']);
        $returnValue = 'ab';
        $spy = $this->spyFactory->create(null);
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $parameters, $parameterNames, $arguments));
        $expected->setResponseEvent($this->eventFactory->createReturned($returnValue));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $parameters, $parameterNames, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
    }

    public function testRecordException()
    {
        $exception = new RuntimeException('You done goofed.');
        $callback = function () use ($exception) {
            throw $exception;
        };
        $parameters = [];
        $parameterNames = [];
        $arguments = Arguments::create(['a', 'b']);
        $spy = $this->spyFactory->create(null);
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $parameters, $parameterNames, $arguments));
        $expected->setResponseEvent($this->eventFactory->createThrew($exception));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $parameters, $parameterNames, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
    }

    public function testRecordEngineErrorException()
    {
        $exception = new Error('You done goofed.');
        $callback = function () use ($exception) {
            throw $exception;
        };
        $parameters = [];
        $parameterNames = [];
        $arguments = Arguments::create(['a', 'b']);
        $spy = $this->spyFactory->create(null);
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $parameters, $parameterNames, $arguments));
        $expected->setResponseEvent($this->eventFactory->createThrew($exception));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $parameters, $parameterNames, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
    }

    public function testRecordWithGeneratedEvents()
    {
        $callback = function () {
            return;
            yield null;
        };
        $parameters = [];
        $parameterNames = [];
        $arguments = Arguments::create(['a', 'b']);
        $generator = call_user_func($callback);
        $spy = $this->spyFactory->create(null);
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $parameters, $parameterNames, $arguments));
        $expected->setResponseEvent($this->eventFactory->createReturned($generator));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $parameters, $parameterNames, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
    }

    public function testCreateGeneratedEvent()
    {
        $generatorFactory = function () {
            return;
            yield null;
        };
        $generator = call_user_func($generatorFactory);
        $expected = new ReturnedEvent(0, 0.0, $generator);
        $actual = $this->eventFactory->createReturned($generator);

        $this->assertEquals($expected, $actual);
    }
}
