<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hamcrest;

use AllowDynamicProperties;
use Hamcrest\Matcher;
use Hamcrest\Util;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class HamcrestMatcherDriverTest extends TestCase
{
    protected function setUp(): void
    {
        Util::registerGlobalFunctions();

        $this->subject = new HamcrestMatcherDriver();

        $this->matcher = equalTo('x');
    }

    public function testIsAvailable()
    {
        $this->assertTrue($this->subject->isAvailable());
    }

    public function testMatcherClassNames()
    {
        $this->assertSame([Matcher::class], $this->subject->matcherClassNames());
    }

    public function testWrapMatcher()
    {
        $this->assertEquals(new HamcrestMatcher($this->matcher), $this->subject->wrapMatcher($this->matcher));
    }
}
