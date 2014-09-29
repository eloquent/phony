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
use ReflectionMethod;

class CallFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new CallFactory();
    }

    public function testCreate()
    {
        $subject = new ReflectionMethod(__METHOD__);
        $exception = new Exception();
        $thisValue = (object) array();
        $expected =
            new Call($subject, array('argumentA', 'argumentB'), 'returnValue', 0, 1.11, 2.22, $exception, $thisValue);
        $actual = $this->subject
            ->create($subject, array('argumentA', 'argumentB'), 'returnValue', 0, 1.11, 2.22, $exception, $thisValue);

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
