<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Generator;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MockGenerator();
    }

    public function generateData()
    {
        $fixturePath = __DIR__ . '/../../../fixture';
        $data = array();

        foreach (scandir($fixturePath . '/mock-generator') as $testName) {
            if ('.' === $testName[0]) {
                continue;
            }

            $testName = 'mock-generator/' . $testName;
            $data[$testName] = array($testName);
        }

        if (version_compare(PHP_VERSION, '5.4.0-dev', '>=')) {
            foreach (
                scandir($fixturePath . '/mock-generator-traits') as $testName
            ) {
                if ('.' === $testName[0]) {
                    continue;
                }

                $testName = 'mock-generator-traits/' . $testName;
                $data[$testName] = array($testName);
            }
        }

        if (defined('HHVM_VERSION')) {
            foreach (
                scandir($fixturePath . '/mock-generator-hhvm') as $testName
            ) {
                if ('.' === $testName[0]) {
                    continue;
                }

                $testName = 'mock-generator-hhvm/' . $testName;
                $data[$testName] = array($testName);
            }
        }

        return $data;
    }

    /**
     * @dataProvider generateData
     */
    public function testGenerate($testName)
    {
        $fixturePath = __DIR__ . '/../../../fixture';
        $builder = require $fixturePath . '/' . $testName . '/builder.php';
        $expected = file_get_contents($fixturePath . '/' . $testName . '/expected.php');
        $actual = $this->subject->generate($builder);

        $this->assertSame($expected, "<?php\n\n" . $actual);

        eval($actual);

        $this->assertTrue(class_exists($builder->className()));
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
