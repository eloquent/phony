<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection;

use Eloquent\Phony\Reflection\Exception\UndefinedFeatureException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FeatureDetectorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->features = [
            'a' => function () { return true; },
            'b' => function () { return false; },
            'c' => function () { return true; },
            'd' => function () { return false; },
        ];
        $this->supported = ['c' => false, 'd' => true];
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
        $this->assertSame([], $this->subject->supported());
    }

    public function testAddFeature()
    {
        $this->subject = new FeatureDetector([]);
        $this->subject->addFeature('e', 'is_object');
        $this->subject->addFeature('f', 'is_string');

        $this->assertSame(['e' => 'is_object', 'f' => 'is_string'], $this->subject->features());
        $this->assertTrue($this->subject->isSupported('e'));
        $this->assertTrue($this->subject->isSupported('e'));
        $this->assertFalse($this->subject->isSupported('f'));
        $this->assertFalse($this->subject->isSupported('f'));
        $this->assertSame(['e' => true, 'f' => false], $this->subject->supported());
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
        $this->assertSame(['c' => false, 'd' => true, 'a' => true, 'b' => false], $this->subject->supported());
    }

    public function testIsSupportedFailureUndefined()
    {
        $this->subject = new FeatureDetector($this->features, $this->supported);

        $this->expectException(UndefinedFeatureException::class);
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
    public function testFeatureDetection($feature, $minimum, $maximum, $exclude)
    {
        $expected = version_compare(PHP_VERSION, $minimum, '>=') &&
            version_compare(PHP_VERSION, $maximum, '<') &&
            $this->checkVersionIncluded(PHP_VERSION, $exclude);

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
        $this->assertTrue($this->checkVersionIncluded('5.4.3', []));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', ['4']));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', ['5.3']));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', ['5.4.2']));
        $this->assertTrue($this->checkVersionIncluded('5.4.3', ['5.4.3.2']));

        $this->assertFalse($this->checkVersionIncluded('5.4.3', ['5']));
        $this->assertFalse($this->checkVersionIncluded('5.4.3', ['5.4']));
        $this->assertFalse($this->checkVersionIncluded('5.4.3', ['5.4.3']));
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
