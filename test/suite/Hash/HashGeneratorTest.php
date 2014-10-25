<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Hash;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class HashGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new HashGenerator();
    }

    public function testHash()
    {
        $object = (object) array();
        $valueA = array('a', 'b', $object);
        $valueB = array('a', 'b', $object);
        $valueC = array('a', 'b', (object) array());

        $this->assertSame($this->subject->hash($valueA), $this->subject->hash($valueB));
        $this->assertNotSame($this->subject->hash($valueA), $this->subject->hash($valueC));
        $this->assertRegExp('/^[[:xdigit:]]{32}$/', $this->subject->hash($valueA));
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
