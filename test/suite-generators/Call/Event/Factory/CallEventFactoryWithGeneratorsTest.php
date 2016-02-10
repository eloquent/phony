<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event\Factory;

use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestClock;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * @covers \Eloquent\Phony\Call\Event\Factory\CallEventFactory
 */
class CallEventFactoryWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sequencer = new Sequencer();
        $this->clock = new TestClock();
        $this->subject = new CallEventFactory($this->sequencer, $this->clock);

        $this->exception = new RuntimeException('You done goofed.');
    }

    public function testCreateGeneratedEvent()
    {
        $generatorFactory = eval('return function () { return; yield null; };');
        $generator = call_user_func($generatorFactory);
        $expected = new ReturnedEvent(0, 0.0, $generator);
        $actual = $this->subject->createGenerated($generator);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateGeneratedEventDefaults()
    {
        $generatorFactory = eval('return function () { return; yield null; };');
        $generator = call_user_func($generatorFactory);
        $expected = new ReturnedEvent(0, 0.0, $generator);
        $actual = $this->subject->createGenerated();

        $this->assertEquals($expected, $actual);
        $this->assertSame(array(), iterator_to_array($actual->value()));
    }
}
