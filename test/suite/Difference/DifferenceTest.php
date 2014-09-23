<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Difference;

use PHPUnit_Framework_TestCase;

class DifferenceTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->from = array('lineA', 'lineB');
        $this->to = array('lineC', 'lineD');
        $this->subject = new Difference($this->from, $this->to);
    }

    public function testConstructor()
    {
        $this->assertSame($this->from, $this->subject->from());
        $this->assertSame($this->to, $this->subject->to());
    }
}
