<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use PHPUnit_Framework_TestCase;

class CaptureMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->value = 'initial';
        $this->matcher = new EqualToMatcher('a');
        $this->subject = new CaptureMatcher($this->value, $this->matcher);
    }

    public function testConstructor()
    {
        $this->assertSame($this->value, $this->subject->value());
        $this->assertSame($this->matcher, $this->subject->matcher());
        $this->assertSame("<'a'>", $this->subject->describe());
        $this->assertSame("<'a'>", strval($this->subject));
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CaptureMatcher();

        $this->assertNull($this->subject->value());
        $this->assertSame(AnyMatcher::instance(), $this->subject->matcher());
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('a'));
        $this->assertSame('a', $this->value);
        $this->assertFalse($this->subject->matches('b'));
        $this->assertSame('b', $this->value);
    }
}
