<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use PHPUnit_Framework_TestCase;

class GeneratedEventTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Generator')) {
            $this->markTestSkipped('Requires generator support.');
        }

        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->generatorFactory = function () { yield 1; };
        $this->generator = call_user_func($this->generatorFactory);
        $this->subject = new GeneratedEvent($this->sequenceNumber, $this->time, $this->generator);
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->generator, $this->subject->generator());
    }
}
