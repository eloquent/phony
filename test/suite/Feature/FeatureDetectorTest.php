<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Feature;

use Eloquent\Fixie\Reader\FixtureReader;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class FeatureDetectorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->features = array(
            'a' => function () { return true; },
            'b' => function () { return false; },
            'c' => function () { return true; },
            'd' => function () { return false; },
        );
        $this->supported = array('c' => false, 'd' => true);
        $this->subject = new FeatureDetector();
    }

    public function testConstructor()
    {
        $this->subject = new FeatureDetector($this->features, $this->supported);

        $this->assertSame($this->features, $this->subject->features());
        $this->assertSame($this->supported, $this->subject->supported());
    }

    public function testConstructorDefaults()
    {
        $this->assertEquals(FeatureDetector::standardFeatures(), $this->subject->features());
        $this->assertSame(array(), $this->subject->supported());
    }

    public function testAddFeature()
    {
        $this->subject = new FeatureDetector(array());
        $this->subject->addFeature('e', 'is_object');
        $this->subject->addFeature('f', 'is_string');

        $this->assertSame(array('e' => 'is_object', 'f' => 'is_string'), $this->subject->features());
        $this->assertTrue($this->subject->isSupported('e'));
        $this->assertTrue($this->subject->isSupported('e'));
        $this->assertFalse($this->subject->isSupported('f'));
        $this->assertFalse($this->subject->isSupported('f'));
        $this->assertSame(array('e' => true, 'f' => false), $this->subject->supported());
    }

    public function testIsSupported()
    {
        $this->subject = new FeatureDetector($this->features, $this->supported);

        $this->assertTrue($this->subject->isSupported('a'));
        $this->assertTrue($this->subject->isSupported('a'));
        $this->assertFalse($this->subject->isSupported('b'));
        $this->assertFalse($this->subject->isSupported('b'));
        $this->assertFalse($this->subject->isSupported('c'));
        $this->assertFalse($this->subject->isSupported('c'));
        $this->assertTrue($this->subject->isSupported('d'));
        $this->assertTrue($this->subject->isSupported('d'));
        $this->assertSame(array('c' => false, 'd' => true, 'a' => true, 'b' => false), $this->subject->supported());
    }

    public function testIsSupportedFailureUndefined()
    {
        $this->subject = new FeatureDetector($this->features, $this->supported);

        $this->setExpectedException('Eloquent\Phony\Feature\Exception\UndefinedFeatureException');
        $this->subject->isSupported('x');
    }

    public function featureData()
    {
        $reader = new FixtureReader();

        return $reader->openFile(__DIR__ . '/../../fixture/feature-detector/features.fixie.yml');
    }

    /**
     * @dataProvider featureData
     */
    public function testFeatureDetection(
        $feature,
        $minimum,
        $maximum,
        $exclude,
        $hhvmMinimum,
        $hhmvMaximum,
        $hhvmExclude
    ) {
        if (defined('HHVM_VERSION')) {
            $expected = $this->checkMinimumVersion(HHVM_VERSION, $hhvmMinimum) &&
                $this->checkMaximumVersion(HHVM_VERSION, $hhmvMaximum) &&
                $this->checkVersionIncluded(HHVM_VERSION, $hhvmExclude);
        } else {
            $expected = $this->checkMinimumVersion(PHP_VERSION, $minimum) &&
                $this->checkMaximumVersion(PHP_VERSION, $maximum) &&
                $this->checkVersionIncluded(PHP_VERSION, $exclude);
        }

        $this->assertSame($expected, $this->subject->isSupported($feature));
    }

    public function testCheckToken()
    {
        $this->assertTrue($this->subject->checkToken('return', 'T_RETURN'));
        $this->assertFalse($this->subject->checkToken('return', 'T_FUNCTION'));
        $this->assertFalse($this->subject->checkToken('return', 'T_JIBBA_JABBA'));
    }

    public function testCheckExpression()
    {
        $this->assertTrue($this->subject->checkExpression(''));
        $this->assertTrue($this->subject->checkExpression('return'));
        $this->assertFalse($this->subject->checkExpression('{'));
    }

    public function testCheckInternalClass()
    {
        $this->assertTrue($this->subject->checkInternalClass('ReflectionClass'));
        $this->assertFalse($this->subject->checkInternalClass(__CLASS__));
        $this->assertFalse($this->subject->checkInternalClass('Nonexistent'));
    }

    public function testCheckInternalMethod()
    {
        $this->assertTrue($this->subject->checkInternalMethod('ReflectionClass', 'isInterface'));
        $this->assertFalse($this->subject->checkInternalMethod(__CLASS__, __FUNCTION__));
        $this->assertFalse($this->subject->checkInternalMethod('ReflectionClass', 'nonexistent'));
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

    // end of feature detector tests

    public function testCheckMinimumVersion()
    {
        $this->assertTrue($this->checkMinimumVersion('5.0.0-dev~nightly', '5'));
        $this->assertTrue($this->checkMinimumVersion('5.99999', '5'));
        $this->assertFalse($this->checkMinimumVersion('4.99999', '5'));

        $this->assertTrue($this->checkMinimumVersion('5.4.0-dev~nightly', '5.4'));
        $this->assertTrue($this->checkMinimumVersion('5.99999', '5.4'));
        $this->assertFalse($this->checkMinimumVersion('5.3.99999', '5.4'));

        $this->assertTrue($this->checkMinimumVersion('5.5.0-dev~nightly', '5.5'));
        $this->assertTrue($this->checkMinimumVersion('5.99999', '5.5'));
        $this->assertFalse($this->checkMinimumVersion('5.4.99999', '5.5'));

        $this->assertTrue($this->checkMinimumVersion('5.6.7-dev~nightly', '5.6.7'));
        $this->assertTrue($this->checkMinimumVersion('5.99999', '5.6.7'));
        $this->assertFalse($this->checkMinimumVersion('5.6.6.99999', '5.6.7'));

        $this->assertTrue($this->checkMinimumVersion('4.0.0', true));
        $this->assertTrue($this->checkMinimumVersion('6.0.0', true));

        $this->assertFalse($this->checkMinimumVersion('4.0.0', false));
        $this->assertFalse($this->checkMinimumVersion('6.0.0', false));
    }

    public function testCheckMaximumVersion()
    {
        $this->assertTrue($this->checkMaximumVersion('5.0.0-dev~nightly', '5'));
        $this->assertTrue($this->checkMaximumVersion('5.99999', '5'));
        $this->assertFalse($this->checkMaximumVersion('6.0.0-dev~nightly', '5'));

        $this->assertTrue($this->checkMaximumVersion('5.4.0-dev~nightly', '5.4'));
        $this->assertTrue($this->checkMaximumVersion('5.4.99999', '5.4'));
        $this->assertFalse($this->checkMaximumVersion('5.5.0-dev~nightly', '5.4'));

        $this->assertTrue($this->checkMaximumVersion('5.5.0-dev~nightly', '5.5'));
        $this->assertTrue($this->checkMaximumVersion('5.5.99999', '5.5'));
        $this->assertFalse($this->checkMaximumVersion('5.6.0-dev~nightly', '5.5'));

        $this->assertTrue($this->checkMaximumVersion('5.6.7-dev~nightly', '5.6.7'));
        $this->assertTrue($this->checkMaximumVersion('5.6.7.99999', '5.6.7'));
        $this->assertFalse($this->checkMaximumVersion('5.6.8-dev~nightly', '5.6.7'));

        $this->assertTrue($this->checkMaximumVersion('4.0.0', true));
        $this->assertTrue($this->checkMaximumVersion('6.0.0', true));

        $this->assertFalse($this->checkMaximumVersion('4.0.0', false));
        $this->assertFalse($this->checkMaximumVersion('6.0.0', false));
    }

    public function testCheckVersionIncluded()
    {
        $this->assertTrue($this->checkVersionIncluded('5.4.3', array()));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', array('4')));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', array('5.3')));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', array('5.4.2')));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', array('5.4.3.2')));

        $this->assertFalse($this->checkVersionIncluded('5.4.3', array('5')));
        $this->assertFalse($this->checkVersionIncluded('5.4.3', array('5.4')));
        $this->assertFalse($this->checkVersionIncluded('5.4.3', array('5.4.3')));
    }

    protected function checkMinimumVersion($version, $minimum)
    {
        if (true === $minimum) {
            return true;
        }
        if (false === $minimum) {
            return false;
        }

        return version_compare($version, $this->minimumVersion($minimum), '>');
    }

    protected function minimumVersion($minimum)
    {
        $parts = explode('.', $minimum);
        $partCount = count($parts);
        $parts[$partCount - 1] = strval(intval($parts[$partCount - 1]) - 1);
        $parts[] = '99999';

        return implode('.', $parts);
    }

    protected function checkMaximumVersion($version, $maximum)
    {
        if (true === $maximum) {
            return true;
        }
        if (false === $maximum) {
            return false;
        }

        return version_compare($version, $this->maximumVersion($maximum), '<=');
    }

    protected function maximumVersion($maximum)
    {
        return $maximum . '.99999';
    }

    protected function checkVersionIncluded($version, $exclude)
    {
        foreach ($exclude as $exclusion) {
            if (substr($version, 0, strlen($exclusion)) === $exclusion) {
                return false;
            }
        }

        return true;
    }
}
