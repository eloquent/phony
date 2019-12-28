<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\TestCallEventFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Eloquent\Phony\Call\CallFactory
 */
class CallFactoryWithGeneratorsTest extends TestCase
{
    protected function setUp(): void
    {
        $this->eventFactory = new TestCallEventFactory();
        $this->invoker = new Invoker();
        $this->subject = new CallFactory($this->eventFactory, $this->invoker);

        $this->spyFactory = SpyFactory::instance();
        $this->exception = new RuntimeException('You done goofed.');
    }

    public function testRecordWithGeneratedEvents()
    {
        $callback = function () { return; yield null; };
        $arguments = Arguments::create(['a', 'b']);
        $generator = call_user_func($callback);
        $spy = $this->spyFactory->create(null);
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $arguments));
        $expected->setResponseEvent($this->eventFactory->createReturned($generator));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([$expected], $spy->allCalls());
    }

    public function testCreateGeneratedEvent()
    {
        $generatorFactory = function () { return; yield null; };
        $generator = call_user_func($generatorFactory);
        $expected = new ReturnedEvent(0, 0.0, $generator);
        $actual = $this->eventFactory->createReturned($generator);

        $this->assertEquals($expected, $actual);
    }
}
