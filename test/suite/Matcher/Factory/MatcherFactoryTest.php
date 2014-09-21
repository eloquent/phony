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
use Eloquent\Phony\Matcher\Integration\HamcrestMatcher;
use Eloquent\Phony\Matcher\Integration\MockeryMatcher;
use Eloquent\Phony\Matcher\Integration\PhakeMatcher;
use Eloquent\Phony\Matcher\Integration\PhpunitMatcher;
use Eloquent\Phony\Matcher\Integration\ProphecyMatcher;
use Eloquent\Phony\Matcher\Integration\SimpletestMatcher;
use EqualExpectation;
use Hamcrest\Core\IsEqual;
use Mockery\Matcher\MustBe;
use PHPUnit_Framework_Constraint_IsEqual;
use PHPUnit_Framework_TestCase;
use Phake_Matchers_EqualsMatcher;
use Prophecy\Argument\Token\IdenticalValueToken;
use ReflectionClass;

class MatcherFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MatcherFactory();
    }

    public function testConstructor()
    {
        $integrationMap = array('className' => 'wrapperClassName');
        $this->subject = new MatcherFactory($integrationMap);

        $this->assertSame($integrationMap, $this->subject->integrationMap());
    }

    public function testConstructorDefaults()
    {
        $this->assertSame(MatcherFactory::defaultIntegrationMap(), $this->subject->integrationMap());
    }

    public function testSetIntegrationMap()
    {
        $integrationMap = array('className' => 'wrapperClassName');
        $this->subject->setIntegrationMap($integrationMap);

        $this->assertSame($integrationMap, $this->subject->integrationMap());
    }

    public function testAddIntegrationMapEntry()
    {
        $integrationMap = array('classNameA' => 'wrapperClassNameA');
        $this->subject->setIntegrationMap($integrationMap);
        $this->subject->addIntegrationMapEntry('classNameB', 'wrapperClassNameB');
        $expected = array('classNameA' => 'wrapperClassNameA', 'classNameB' => 'wrapperClassNameB');

        $this->assertSame($expected, $this->subject->integrationMap());
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

    public function testAdaptHamcrestMatcher()
    {
        $matcher = new IsEqual('value');
        $expected = new HamcrestMatcher($matcher);
        $actual = $this->subject->adapt($matcher);

        $this->assertEquals($expected, $actual);
    }

    public function testAdaptPhpunitConstraint()
    {
        $matcher = new PHPUnit_Framework_Constraint_IsEqual('value');
        $expected = new PhpunitMatcher($matcher);
        $actual = $this->subject->adapt($matcher);

        $this->assertEquals($expected, $actual);
    }

    public function testAdaptPhakeMatcher()
    {
        $matcher = new Phake_Matchers_EqualsMatcher('value');
        $expected = new PhakeMatcher($matcher);
        $actual = $this->subject->adapt($matcher);

        $this->assertEquals($expected, $actual);
    }

    public function testAdaptProphecyToken()
    {
        $matcher = new IdenticalValueToken('value');
        $expected = new ProphecyMatcher($matcher);
        $actual = $this->subject->adapt($matcher);

        $this->assertEquals($expected, $actual);
    }

    public function testAdaptMockeryMatcher()
    {
        $matcher = new MustBe('value');
        $expected = new MockeryMatcher($matcher);
        $actual = $this->subject->adapt($matcher);

        $this->assertEquals($expected, $actual);
    }

    public function testAdaptSimpletestExpectation()
    {
        $matcher = new EqualExpectation('value');
        $expected = new SimpletestMatcher($matcher);
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

    public function testDefaultIntegrationMap()
    {
        $expected = array(
            'Hamcrest\Matcher' =>
                'Eloquent\Phony\Matcher\Integration\HamcrestMatcher',
            'PHPUnit_Framework_Constraint' =>
                'Eloquent\Phony\Matcher\Integration\PhpunitMatcher',
            'Phake_Matchers_IArgumentMatcher' =>
                'Eloquent\Phony\Matcher\Integration\PhakeMatcher',
            'Prophecy\Argument\Token\TokenInterface' =>
                'Eloquent\Phony\Matcher\Integration\ProphecyMatcher',
            'Mockery\Matcher\MatcherAbstract' =>
                'Eloquent\Phony\Matcher\Integration\MockeryMatcher',
            'SimpleExpectation' =>
                'Eloquent\Phony\Matcher\Integration\SimpletestMatcher',
        );

        $this->assertSame($expected, MatcherFactory::defaultIntegrationMap());
    }
}
