<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Prophecy;

use Eloquent\Phony\Matcher\WildcardMatcher;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use ReflectionClass;

class ProphecyMatcherDriverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new ProphecyMatcherDriver();

        $this->matcher = Argument::is('value');
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->subject->isSupported($this->matcher));
        $this->assertFalse($this->subject->isSupported((object) array()));
    }

    public function testAdapt()
    {
        $object = (object) array();
        $expected = new ProphecyMatcher($this->matcher);

        $this->assertTrue($this->subject->adapt($this->matcher));
        $this->assertEquals($expected, $this->matcher);
        $this->assertFalse($this->subject->adapt($object));
    }

    public function testAdaptWildcard()
    {
        $this->matcher = Argument::cetera();

        $this->assertTrue($this->subject->adapt($this->matcher));
        $this->assertSame(WildcardMatcher::instance(), $this->matcher);
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
