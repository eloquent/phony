<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Mockery;

use Mockery;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockeryMatcherDriverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MockeryMatcherDriver();

        $this->matcher = Mockery::mustBe('x');
    }

    public function testIsAvailable()
    {
        $this->assertTrue($this->subject->isAvailable());
    }

    public function testMatcherClassNames()
    {
        $this->assertSame(array('Mockery\Matcher\MatcherAbstract'), $this->subject->matcherClassNames());
    }

    public function testWrapMatcher()
    {
        $this->assertEquals(new MockeryMatcher($this->matcher), $this->subject->wrapMatcher($this->matcher));
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
