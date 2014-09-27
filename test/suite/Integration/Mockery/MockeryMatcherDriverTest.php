<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
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
    }

    public function testAdapt()
    {
        $object = (object) array();
        $matcher = Mockery::mustBe('value');
        $expected = new MockeryMatcher($matcher);

        $this->assertTrue($this->subject->adapt($matcher));
        $this->assertEquals($expected, $matcher);
        $this->assertFalse($this->subject->adapt($object));
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
