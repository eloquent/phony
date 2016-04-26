<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Simpletest;

use EqualExpectation;
use PHPUnit_Framework_TestCase;

class SimpletestMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcher = new EqualExpectation('x');
        $this->subject = new SimpletestMatcher($this->matcher);

        $this->description = '<EqualExpectation>';
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcher, $this->subject->matcher());
        $this->assertSame($this->description, $this->subject->describe());
        $this->assertSame($this->description, strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('x'));
        $this->assertFalse($this->subject->matches('y'));
    }
}
