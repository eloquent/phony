<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Test\TestCallEventFactory;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * @covers \Eloquent\Phony\Call\CallFactory
 */
class CallFactoryWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
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
        $arguments = Arguments::create(array('a', 'b'));
        $generator = call_user_func($callback);
        $spy = $this->spyFactory->create();
        $expected = new CallData(0, $this->eventFactory->createCalled($spy, $arguments));
        $expected->setResponseEvent($this->eventFactory->createReturned($generator));
        $this->eventFactory->reset();
        $actual = $this->subject->record($callback, $arguments, $spy);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(array($expected), $spy->allCalls());
    }

    public function testCreateGeneratedEvent()
    {
        $generatorFactory = eval('return function () { return; yield null; };');
        $generator = call_user_func($generatorFactory);
        $expected = new ReturnedEvent(0, 0.0, $generator);
        $actual = $this->eventFactory->createReturned($generator);

        $this->assertEquals($expected, $actual);
    }
}
