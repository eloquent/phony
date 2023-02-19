<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use AllowDynamicProperties;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class AnyMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        $this->subject = new AnyMatcher();
    }

    public function testConstructor()
    {
        $this->assertSame('<any>', $this->subject->describe());
        $this->assertSame('<any>', strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('x'));
        $this->assertTrue($this->subject->matches('y'));
    }
}
