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

use Eloquent\Phony\Integration\Hamcrest\HamcrestMatcher;
use Eloquent\Phony\Integration\Hamcrest\HamcrestMatcherDriver;
use Eloquent\Phony\Integration\Phpunit\PhpunitMatcher;
use Eloquent\Phony\Integration\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Hamcrest\Core\IsEqual;
use PHPUnit_Framework_Constraint_IsEqual;
use PHPUnit_Framework_TestCase;

class MatcherFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->driverA = new PhpunitMatcherDriver;
        $this->driverB = new HamcrestMatcherDriver;
        $this->drivers = array($this->driverA, $this->driverB);
        $this->subject = new MatcherFactory($this->drivers);
    }

    public function testConstructor()
    {
        $this->assertSame($this->drivers, $this->subject->drivers());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MatcherFactory;

        $this->assertSame(array(), $this->subject->drivers());
    }

    public function testSetMatcherDrivers()
    {
        $this->subject->setMatcherDrivers(array());

        $this->assertSame(array(), $this->subject->drivers());

        $this->subject->setMatcherDrivers($this->drivers);

        $this->assertSame($this->drivers, $this->subject->drivers());
    }

    public function testAddMatcherDriver()
    {
        $this->subject->setMatcherDrivers(array());
        $this->subject->addMatcherDriver($this->driverA);

        $this->assertSame(array($this->driverA), $this->subject->drivers());

        $this->subject->addMatcherDriver($this->driverB);

        $this->assertSame($this->drivers, $this->subject->drivers());
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

    public function testAdaptViaDriver()
    {
        $phpunitConstraint = new PHPUnit_Framework_Constraint_IsEqual('value');
        $hamcrestMatcher = new IsEqual('value');

        $this->assertEquals(new PhpunitMatcher($phpunitConstraint), $this->subject->adapt($phpunitConstraint));
        $this->assertEquals(new HamcrestMatcher($hamcrestMatcher), $this->subject->adapt($hamcrestMatcher));
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
}
