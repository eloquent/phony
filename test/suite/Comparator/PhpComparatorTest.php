<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Comparator;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class PhpComparatorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->subject = new PhpComparator();
    }

    public function testCompare()
    {
        $this->assertLessThan(0, $this->subject->compare(10, 20));
        $this->assertGreaterThan(0, $this->subject->compare(20, 10));
        $this->assertSame(0, $this->subject->compare(10, 10));
    }

    public function testInvoke()
    {
        $this->assertLessThan(0, call_user_func($this->subject, 10, 20));
        $this->assertGreaterThan(0, call_user_func($this->subject, 20, 10));
        $this->assertSame(0, call_user_func($this->subject, 10, 10));
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
