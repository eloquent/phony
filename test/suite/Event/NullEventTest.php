<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use PHPUnit_Framework_TestCase;

class NullEventTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new NullEvent();
    }

    public function testConstructor()
    {
        $this->assertSame(-1, $this->subject->sequenceNumber());
        $this->assertEquals(-1.0, $this->subject->time());
    }
}
