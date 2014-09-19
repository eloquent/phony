<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Clock;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SystemClockTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
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
        $this->subject = new SystemClock;

        $this->assertInternalType('float', $this->subject->time());
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
