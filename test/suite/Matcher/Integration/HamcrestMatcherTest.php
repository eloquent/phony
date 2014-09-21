<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Integration;

use Hamcrest\Core\IsEqual;
use PHPUnit_Framework_TestCase;

class HamcrestMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcher = new IsEqual('value');
        $this->subject = new HamcrestMatcher($this->matcher);
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcher, $this->subject->matcher());
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('value'));
        $this->assertFalse($this->subject->matches('anotherValue'));
    }
}
