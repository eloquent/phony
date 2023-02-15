<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hamcrest;

use AllowDynamicProperties;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class HamcrestMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        $this->matcher = equalTo('value');
        $this->subject = new HamcrestMatcher($this->matcher);
    }

    public function testConstructor()
    {
        $this->assertSame('<"value">', $this->subject->describe());
        $this->assertSame('<"value">', strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('value'));
        $this->assertFalse($this->subject->matches('x'));
    }
}
