<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Test\TestExternalMatcher;
use PHPUnit_Framework_TestCase;

class WrappedMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new WrappedMatcher(new TestExternalMatcher());
    }

    public function testConstructor()
    {
        $this->assertSame('<Eloquent\Phony\Test\TestExternalMatcher>', $this->subject->describe());
        $this->assertSame('<Eloquent\Phony\Test\TestExternalMatcher>', strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('value'));
        $this->assertFalse($this->subject->matches('x'));
    }
}
