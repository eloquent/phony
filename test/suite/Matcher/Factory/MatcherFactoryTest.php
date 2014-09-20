<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Factory;

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\WrappedMatcher;
use Hamcrest\Core\IsEqual;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MatcherFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MatcherFactory();
    }

    public function testAdapt()
    {
        $value = 'value';
        $matcher = new EqualToMatcher($value);
        $adaptedValue = $this->subject->adapt($value);

        $this->assertSame($matcher, $this->subject->adapt($matcher));
        $this->assertNotSame($matcher, $adaptedValue);
        $this->assertEquals($matcher, $adaptedValue);
    }

    public function testAdaptHamcrestMatcher()
    {
        $matcher = new IsEqual('value');
        $expected = new WrappedMatcher($matcher);
        $actual = $this->subject->adapt($matcher);

        $this->assertEquals($expected, $actual);
    }

    public function testAdaptAll()
    {
        $valueA = 'valueA';
        $valueB = new EqualToMatcher('valueB');
        $values = array($valueA, $valueB);
        $actual = $this->subject->adaptAll($values);
        $expected = array(new EqualToMatcher($valueA), $valueB);

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
