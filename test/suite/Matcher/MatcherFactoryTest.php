<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Integration\HamcrestMatcherDriver;
use Eloquent\Phony\Phpunit\Phony;
use Eloquent\Phony\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Simpletest\SimpletestMatcherDriver;
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
        $this->anyMatcher = new AnyMatcher();
        $this->wildcardAnyMatcher = WildcardMatcher::instance();
        $this->exporter = InlineExporter::instance();
        $this->subject = new MatcherFactory($this->anyMatcher, $this->wildcardAnyMatcher, $this->exporter);

        $this->driverA = new TestMatcherDriverA();
        $this->driverB = new TestMatcherDriverB();
        $this->drivers = array($this->driverA, $this->driverB);

        $this->featureDetector = FeatureDetector::instance();
    }

    public function testAddMatcherDriver()
    {
        $this->subject = new MatcherFactory($this->anyMatcher, $this->wildcardAnyMatcher, $this->exporter);
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
        $this->subject = new MatcherFactory($this->anyMatcher, $this->wildcardAnyMatcher, $this->exporter);
        $this->subject->addDefaultMatcherDrivers();

        $this->assertSame(
            array(
                HamcrestMatcherDriver::instance(),
                PhpunitMatcherDriver::instance(),
                SimpletestMatcherDriver::instance()
            ),
            $this->subject->drivers()
        );
    }

    public function testIsMatcher()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);

        $this->assertTrue($this->subject->isMatcher(new EqualToMatcher('a', true, $this->exporter)));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherA()));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherB()));
        $this->assertFalse($this->subject->isMatcher((object) array()));
    }

    public function testAdapt()
    {
        $value = (object) array('key' => 'value');
        $matcher = new EqualToMatcher($value, true, $this->exporter);
        $adaptedValue = $this->subject->adapt($value);

        $this->assertSame($matcher, $this->subject->adapt($matcher));
        $this->assertNotSame($matcher, $adaptedValue);
        $this->assertEquals($matcher, $adaptedValue);
    }

    public function testAdaptBoolean()
    {
        $value = true;
        $matcher = new EqualToMatcher($value, true, $this->exporter);
        $adaptedValue = $this->subject->adapt($value);

        $this->assertEquals($matcher, $adaptedValue);
    }

    public function testAdaptViaDriver()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);
        $driverAMatcher = new TestMatcherA();
        $driverBMatcher = new TestMatcherB();

        $this->assertEquals(new EqualToMatcher('a', false, $this->exporter), $this->subject->adapt($driverAMatcher));
        $this->assertEquals(new EqualToMatcher('b', false, $this->exporter), $this->subject->adapt($driverBMatcher));
    }

    public function testAdaptSpecialCases()
    {
        $this->assertSame($this->wildcardAnyMatcher, $this->subject->adapt('*'));
        $this->assertSame($this->anyMatcher, $this->subject->adapt('~'));
    }

    public function testAdaptAll()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);

        $valueB = new EqualToMatcher('b', true, $this->exporter);
        $valueC = (object) array();
        $valueD = Phony::mock();
        $values = array(
            'a',
            $valueB,
            $valueC,
            $valueD,
            new TestMatcherA(),
            '*',
            '~',
        );
        $actual = $this->subject->adaptAll($values);
        $expected = array(
            new EqualToMatcher('a', true, $this->exporter),
            $valueB,
            new EqualToMatcher($valueC, true, $this->exporter),
            new EqualToMatcher($valueD, true, $this->exporter),
            new EqualToMatcher('a', false, $this->exporter),
            WildcardMatcher::instance(),
            $this->anyMatcher,
        );

        $this->assertEquals($expected, $actual);
    }

    public function testEqualTo()
    {
        $expected = new EqualToMatcher('x', false, $this->exporter);

        $this->assertEquals($expected, $this->subject->equalTo('x'));
    }

    public function testWildcard()
    {
        $expected = new WildcardMatcher(new EqualToMatcher('x', true, $this->exporter), 111, 222);

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
                PhpunitMatcherDriver::instance(),
                SimpletestMatcherDriver::instance(),
            ),
            $instance->drivers()
        );
    }
}
