<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Factory;

use Eloquent\Phony\Integration\Counterpart\CounterpartMatcherDriver;
use Eloquent\Phony\Integration\Hamcrest\HamcrestMatcherDriver;
use Eloquent\Phony\Integration\Mockery\MockeryMatcherDriver;
use Eloquent\Phony\Integration\Phake\PhakeMatcherDriver;
use Eloquent\Phony\Integration\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Integration\Prophecy\ProphecyMatcherDriver;
use Eloquent\Phony\Integration\Simpletest\SimpletestMatcherDriver;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Test\TestMatcherA;
use Eloquent\Phony\Test\TestMatcherB;
use Eloquent\Phony\Test\TestMatcherDriverA;
use Eloquent\Phony\Test\TestMatcherDriverB;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MatcherFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->driverA = new TestMatcherDriverA();
        $this->driverB = new TestMatcherDriverB();
        $this->drivers = array($this->driverA, $this->driverB);
        $this->anyMatcher = new AnyMatcher();
        $this->wildcardAnyMatcher = new WildcardMatcher();
        $this->subject = new MatcherFactory($this->drivers, $this->anyMatcher, $this->wildcardAnyMatcher);
    }

    public function testConstructor()
    {
        $this->assertSame($this->drivers, $this->subject->drivers());
        $this->assertSame($this->anyMatcher, $this->subject->any());
        $this->assertSame($this->wildcardAnyMatcher, $this->subject->wildcard());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MatcherFactory();

        $this->assertSame(array(), $this->subject->drivers());
        $this->assertSame(AnyMatcher::instance(), $this->subject->any());
        $this->assertSame(WildcardMatcher::instance(), $this->subject->wildcard());
    }

    public function testAddMatcherDriver()
    {
        $this->subject = new MatcherFactory(null, $this->anyMatcher, $this->wildcardAnyMatcher);
        $this->subject->addMatcherDriver($this->driverA);

        $this->assertSame(array($this->driverA), $this->subject->drivers());

        $this->subject->addMatcherDriver($this->driverB);

        $this->assertSame($this->drivers, $this->subject->drivers());
    }

    /**
     * @requires PHP 5.4.0-dev
     */
    public function testAddDefaultMatcherDrivers()
    {
        $this->subject = new MatcherFactory(null, $this->anyMatcher, $this->wildcardAnyMatcher);
        $this->subject->addDefaultMatcherDrivers();

        $this->assertSame(
            array(
                HamcrestMatcherDriver::instance(),
                CounterpartMatcherDriver::instance(),
                PhpunitMatcherDriver::instance(),
                SimpletestMatcherDriver::instance(),
                PhakeMatcherDriver::instance(),
                ProphecyMatcherDriver::instance(),
                MockeryMatcherDriver::instance(),
            ),
            $this->subject->drivers()
        );
    }

    public function testIsMatcher()
    {
        $this->assertTrue($this->subject->isMatcher(new EqualToMatcher('a')));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherA()));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherB()));
        $this->assertFalse($this->subject->isMatcher((object) array()));
    }

    public function testAdapt()
    {
        $value = (object) array('key' => 'value');
        $matcher = new EqualToMatcher($value);
        $adaptedValue = $this->subject->adapt($value);

        $this->assertSame($matcher, $this->subject->adapt($matcher));
        $this->assertNotSame($matcher, $adaptedValue);
        $this->assertEquals($matcher, $adaptedValue);
    }

    public function testAdaptBoolean()
    {
        $value = true;
        $matcher = new EqualToMatcher($value);
        $adaptedValue = $this->subject->adapt($value);

        $this->assertEquals($matcher, $adaptedValue);
    }

    public function testAdaptViaDriver()
    {
        $driverAMatcher = new TestMatcherA();
        $driverBMatcher = new TestMatcherB();

        $this->assertEquals(new EqualToMatcher('a'), $this->subject->adapt($driverAMatcher));
        $this->assertEquals(new EqualToMatcher('b'), $this->subject->adapt($driverBMatcher));
    }

    public function testAdaptSpecialCases()
    {
        $this->assertSame($this->wildcardAnyMatcher, $this->subject->adapt('*'));
        $this->assertSame($this->anyMatcher, $this->subject->adapt('~'));
    }

    public function testAdaptAll()
    {
        $valueB = new EqualToMatcher('b');
        $valueC = (object) array();
        $values = array(
            'a',
            $valueB,
            $valueC,
            new TestMatcherA(),
            '*',
            '~',
        );
        $actual = $this->subject->adaptAll($values);
        $expected = array(
            new EqualToMatcher('a'),
            $valueB,
            new EqualToMatcher($valueC),
            new EqualToMatcher('a'),
            WildcardMatcher::instance(),
            $this->anyMatcher,
        );

        $this->assertEquals($expected, $actual);
    }

    public function testEqualTo()
    {
        $expected = new EqualToMatcher('x');

        $this->assertEquals($expected, $this->subject->equalTo('x'));
    }

    public function testWildcard()
    {
        $expected = new WildcardMatcher(new EqualToMatcher('x'), 111, 222);

        $this->assertEquals($expected, $this->subject->wildcard('x', 111, 222));
    }

    public function testWildcardWithNullValue()
    {
        $expected = new WildcardMatcher($this->anyMatcher, 111, 222);

        $this->assertEquals($expected, $this->subject->wildcard(null, 111, 222));
    }

    /**
     * @requires PHP 5.4.0-dev
     */
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
        $this->assertSame(
            array(
                HamcrestMatcherDriver::instance(),
                CounterpartMatcherDriver::instance(),
                PhpunitMatcherDriver::instance(),
                SimpletestMatcherDriver::instance(),
                PhakeMatcherDriver::instance(),
                ProphecyMatcherDriver::instance(),
                MockeryMatcherDriver::instance(),
            ),
            $instance->drivers()
        );
    }
}
