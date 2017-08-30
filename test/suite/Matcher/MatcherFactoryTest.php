<?php

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Hamcrest\HamcrestMatcherDriver;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\TestMatcherA;
use Eloquent\Phony\Test\TestMatcherB;
use Eloquent\Phony\Test\TestMatcherDriverA;
use Eloquent\Phony\Test\TestMatcherDriverB;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MatcherFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->anyMatcher = new AnyMatcher();
        $this->wildcardAnyMatcher = WildcardMatcher::instance();
        $this->exporter = InlineExporter::instance();
        $this->subject = new MatcherFactory($this->anyMatcher, $this->wildcardAnyMatcher, $this->exporter);

        $this->driverA = new TestMatcherDriverA();
        $this->driverB = new TestMatcherDriverB();
        $this->drivers = [$this->driverA, $this->driverB];

        $this->featureDetector = FeatureDetector::instance();
    }

    public function testAddMatcherDriver()
    {
        $this->subject = new MatcherFactory($this->anyMatcher, $this->wildcardAnyMatcher, $this->exporter);
        $this->subject->addMatcherDriver($this->driverA);

        $this->assertSame([$this->driverA], $this->subject->drivers());

        $this->subject->addMatcherDriver($this->driverB);

        $this->assertSame($this->drivers, $this->subject->drivers());
    }

    public function testIsMatcher()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);

        $this->assertTrue($this->subject->isMatcher(new EqualToMatcher('a', true, $this->exporter)));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherA()));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherB()));
        $this->assertFalse($this->subject->isMatcher((object) []));
    }

    public function testAdapt()
    {
        $value = (object) ['key' => 'value'];
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
        $valueC = (object) [];
        $valueD = Phony::mock();
        $values = [
            'a',
            $valueB,
            $valueC,
            $valueD,
            new TestMatcherA(),
            '*',
            '~',
        ];
        $actual = $this->subject->adaptAll($values);
        $expected = [
            new EqualToMatcher('a', true, $this->exporter),
            $valueB,
            new EqualToMatcher($valueC, true, $this->exporter),
            new EqualToMatcher($valueD, true, $this->exporter),
            new EqualToMatcher('a', false, $this->exporter),
            WildcardMatcher::instance(),
            $this->anyMatcher,
        ];

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
            [
                HamcrestMatcherDriver::instance(),
            ],
            $instance->drivers()
        );
    }
}
