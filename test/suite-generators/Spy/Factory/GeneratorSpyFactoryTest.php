<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class GeneratorSpyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported under HHVM.');
        }

        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->subject = new GeneratorSpyFactory($this->callEventFactory);

        $this->call = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createGenerated()
        );
        $this->callFactory->reset();
    }

    public function testIsSupported()
    {
        $generator = call_user_func(
            function () {
                return;
                yield null;
            }
        );

        $this->assertTrue($this->subject->isSupported($generator));
    }

    public function testCreateWithReturnedEnd()
    {
        $receivedException = new RuntimeException('You done goofed.');
        $generator = call_user_func(
            function () {
                yield 'a';

                try {
                    yield 'b';
                } catch (RuntimeException $receivedException) {}

                yield 'c';
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        try {
            while ($spy->valid()) {
                if (1 === $spy->key()) {
                    $spy->throw($receivedException);
                } else {
                    $spy->send(strtoupper($spy->current()));
                }
            }
        } catch (RuntimeException $caughtException) {}
        $this->callFactory->reset();
        $generatorEvents = array(
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceivedException($receivedException),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        );
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createReturned();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }

    public function testCreateWithThrownExceptionEnd()
    {
        $receivedException = new RuntimeException('You done goofed.');
        $exception = new RuntimeException('Consequences will never be the same.');
        $generator = call_user_func(
            function () use ($exception) {
                yield 'a';

                try {
                    yield 'b';
                } catch (RuntimeException $receivedException) {}

                yield 'c';

                throw $exception;
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        try {
            while ($spy->valid()) {
                if (1 === $spy->key()) {
                    $spy->throw($receivedException);
                } else {
                    $spy->send(strtoupper($spy->current()));
                }
            }
        } catch (RuntimeException $caughtException) {}
        $this->callFactory->reset();
        $generatorEvents = array(
            $this->callEventFactory->createProduced(0, 'a'),
            $this->callEventFactory->createReceived('A'),
            $this->callEventFactory->createProduced(1, 'b'),
            $this->callEventFactory->createReceivedException($receivedException),
            $this->callEventFactory->createProduced(2, 'c'),
            $this->callEventFactory->createReceived('C'),
        );
        foreach ($generatorEvents as $generatorEvent) {
            $generatorEvent->setCall($this->call);
        }
        $endEvent = $this->callEventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }

    public function testCreateWithEmptyGenerator()
    {
        $generator = call_user_func(
            function () {
                return;
                yield null;
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        foreach ($spy as $value) {}
        $this->callFactory->reset();
        $generatorEvents = array();
        $endEvent = $this->callEventFactory->createReturned();
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }

    public function testCreateWithImmediateThrowGenerator()
    {
        $exception = new RuntimeException('You done goofed.');
        $generator = call_user_func(
            function () use ($exception) {
                throw $exception;
                yield null;
            }
        );
        $spy = $this->subject->create($this->call, $generator, true);
        foreach ($spy as $value) {}
        $this->callFactory->reset();
        $generatorEvents = array();
        $endEvent = $this->callEventFactory->createThrew($exception);
        $endEvent->setCall($this->call);

        $this->assertInstanceOf('Generator', $spy);
        $this->assertEquals($generatorEvents, $this->call->traversableEvents());
        $this->assertEquals($endEvent, $this->call->endEvent());
    }
}
