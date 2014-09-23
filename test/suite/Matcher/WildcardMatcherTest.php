<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class WildcardMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcher = new AnyMatcher();
        $this->minimumArguments = 1;
        $this->maximumArguments = 2;
        $this->subject = new WildcardMatcher($this->matcher, $this->minimumArguments, $this->maximumArguments);
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcher, $this->subject->matcher());
        $this->assertSame($this->minimumArguments, $this->subject->minimumArguments());
        $this->assertSame($this->maximumArguments, $this->subject->maximumArguments());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new WildcardMatcher();

        $this->assertSame(AnyMatcher::instance(), $this->subject->matcher());
        $this->assertSame(0, $this->subject->minimumArguments());
        $this->assertNull($this->subject->maximumArguments());
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
