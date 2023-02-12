<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hamcrest;

use Eloquent\Phony\Test\WithDynamicProperties;
use PHPUnit\Framework\TestCase;

class HamcrestMatcherTest extends TestCase
{
    use WithDynamicProperties;

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
