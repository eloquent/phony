<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use EqualExpectation;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SimpletestMatcherDriverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new SimpletestMatcherDriver();

        $this->matcher = new EqualExpectation('value');
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->subject->isSupported($this->matcher));
        $this->assertFalse($this->subject->isSupported((object) array()));
    }

    public function testAdapt()
    {
        $object = (object) array();
        $expected = new SimpletestMatcher($this->matcher);

        $this->assertTrue($this->subject->adapt($this->matcher));
        $this->assertEquals($expected, $this->matcher);
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
