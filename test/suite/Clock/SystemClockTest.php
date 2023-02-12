<?php

declare(strict_types=1);

namespace Eloquent\Phony\Clock;

use Eloquent\Phony\Test\WithDynamicProperties;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SystemClockTest extends TestCase
{
    use WithDynamicProperties;

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

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
