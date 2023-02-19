<?php

declare(strict_types=1);

namespace Eloquent\Phony\Clock;

use AllowDynamicProperties;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class SystemClockTest extends TestCase
{
    protected function setUp(): void
    {
        $time = 0.123;
        $this->microtime = function ($isFloat) use (&$time) {
            if (true === $isFloat) {
                $currentTime = $time;
                $time += 1.0;

                return $currentTime;
            }

            return 'invalid';
        };
        $this->subject = new SystemClock($this->microtime);
    }

    public function testTime()
    {
        $this->assertSame(0.123, $this->subject->time());
        $this->assertSame(1.123, $this->subject->time());
        $this->assertSame(2.123, $this->subject->time());
    }

    public function testTimeReal()
    {
        $this->subject = new SystemClock('microtime');

        $this->assertIsFloat($this->subject->time());
    }
}
