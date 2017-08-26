<?php

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Test\TestExternalMatcher;
use PHPUnit\Framework\TestCase;

class WrappedMatcherTest extends TestCase
{
    protected function setUp()
    {
        $this->matcher = new TestExternalMatcher();
        $this->subject = new WrappedMatcher($this->matcher);
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcher, $this->subject->matcher());
        $this->assertSame('<Eloquent\Phony\Test\TestExternalMatcher>', $this->subject->describe());
        $this->assertSame('<Eloquent\Phony\Test\TestExternalMatcher>', strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('value'));
        $this->assertFalse($this->subject->matches('x'));
    }
}
