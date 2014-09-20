<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\Call;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class CallFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new CallFactory();
    }

    public function testCreate()
    {
        $exception = new Exception();
        $thisValue = (object) array();
        $expected = new Call(array('argumentA', 'argumentB'), 'returnValue', 0, 1.11, 2.22, $exception, $thisValue);
        $actual = $this->subject
            ->create(array('argumentA', 'argumentB'), 'returnValue', 0, 1.11, 2.22, $exception, $thisValue);

        $this->assertEquals($expected, $actual);
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
