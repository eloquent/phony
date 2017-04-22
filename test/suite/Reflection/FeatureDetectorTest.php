<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Reflection;

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
        $this->assertEquals($this->subject->standardFeatures(), $this->subject->features());
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

        $this->setExpectedException('Eloquent\Phony\Reflection\Exception\UndefinedFeatureException');
        $this->subject->isSupported('x');
    }

    public function featureData()
    {
        $json = file_get_contents(__DIR__ . '/../../fixture/feature-detector/features.json');

        return json_decode($json, true);
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
        $hhvmMaximum,
        $hhvmExclude
    ) {
        if (defined('HHVM_VERSION')) {
            $expected = version_compare(HHVM_VERSION, $hhvmMinimum, '>=') &&
                version_compare(HHVM_VERSION, $hhvmMaximum, '<') &&
                $this->checkVersionIncluded(HHVM_VERSION, $hhvmExclude);
        } else {
            $expected = version_compare(PHP_VERSION, $minimum, '>=') &&
                version_compare(PHP_VERSION, $maximum, '<') &&
                $this->checkVersionIncluded(PHP_VERSION, $exclude);
        }

        if ('stdout.ansi' === $feature) {
            if (DIRECTORY_SEPARATOR === '\\') {
                $expected =
                    0 >= version_compare(
                    '10.0.10586',
                    PHP_WINDOWS_VERSION_MAJOR .
                        '.' . PHP_WINDOWS_VERSION_MINOR .
                        '.' . PHP_WINDOWS_VERSION_BUILD
                    ) ||
                    false !== getenv('ANSICON') ||
                    'ON' === getenv('ConEmuANSI') ||
                    'xterm' === getenv('TERM') ||
                    false !== getenv('BABUN_HOME');
            } else {
                $expected = function_exists('posix_isatty') && posix_isatty(STDOUT);
            }
        }

        $this->assertSame($expected, $this->subject->isSupported($feature));
    }

    public function testRuntime()
    {
        if (defined('HHVM_VERSION')) {
            $this->assertSame('hhvm', $this->subject->runtime());
        } else {
            $this->assertSame('php', $this->subject->runtime());
        }
    }

    public function testCheckToken()
    {
        $this->assertTrue($this->subject->checkToken('return', 'T_RETURN'));
        $this->assertFalse($this->subject->checkToken('return', 'T_FUNCTION'));
        $this->assertFalse($this->subject->checkToken('return', 'T_JIBBA_JABBA'));
    }

    public function testCheckStatement()
    {
        $this->assertTrue($this->subject->checkStatement(''));
        $this->assertTrue($this->subject->checkStatement('return'));
        $this->assertFalse($this->subject->checkStatement('{'));

        $error = error_get_last();

        if (function_exists('error_clear_last')) {
            $this->assertNull($error);
        } else {
            $this->assertSame(E_USER_NOTICE, $error['type']);
            $this->assertSame('', $error['message']);
        }
    }

    public function testCheckStatementFailure()
    {
        $this->assertFalse($this->subject->checkStatement('throw new RuntimeException()', false));

        $error = error_get_last();

        if (function_exists('error_clear_last')) {
            $this->assertNull($error);
        } else {
            $this->assertSame(E_USER_NOTICE, $error['type']);
            $this->assertSame('', $error['message']);
        }
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
        $this->assertFalse($this->subject->checkInternalMethod('Nonexistent', 'nonexistent'));
        $this->assertFalse($this->subject->checkInternalMethod('ReflectionClass', 'nonexistent'));
    }

    public function testUniqueSymbolName()
    {
        $actual = $this->subject->uniqueSymbolName();

        $this->assertRegExp('/^_FD_symbol_[[:xdigit:]]{32}$/', $actual);
        $this->assertNotEquals($actual, $this->subject->uniqueSymbolName());
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
