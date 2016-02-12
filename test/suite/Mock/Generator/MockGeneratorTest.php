<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Generator;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Mock\Builder\Definition\MockDefinition;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->signatureInspector = new FunctionSignatureInspector();
        $this->featureDetector = new FeatureDetector();
        $this->subject = new MockGenerator($this->labelSequencer, $this->signatureInspector, $this->featureDetector);
    }

    public function testConstructor()
    {
        $this->assertSame($this->labelSequencer, $this->subject->labelSequencer());
        $this->assertSame($this->signatureInspector, $this->subject->signatureInspector());
        $this->assertSame($this->featureDetector, $this->subject->featureDetector());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockGenerator();

        $this->assertSame(Sequencer::sequence('mock-class-label'), $this->subject->labelSequencer());
        $this->assertSame(FunctionSignatureInspector::instance(), $this->subject->signatureInspector());
        $this->assertSame(FeatureDetector::instance(), $this->subject->featureDetector());
    }

    public function classNameData()
    {
        //                                      types                                    expected
        return array(
            'Anonymous'                => array(array(),                                 'PhonyMock_0'),
            'Extends class'            => array(array('stdClass'),                       'PhonyMock_stdClass_0'),
            'Extends namespaced class' => array(array('Eloquent\Phony\Test\TestClassB'), 'PhonyMock_TestClassB_0'),
            'Inherits interface'       => array(array('Iterator', 'Countable'),          'PhonyMock_Iterator_0'),
        );
    }

    /**
     * @dataProvider classNameData
     */
    public function testClassName($types, $expected)
    {
        $types = array_map(function ($type) { return new ReflectionClass($type); }, $types);
        $definition = new MockDefinition($types);

        $this->assertSame($expected, $this->subject->generateClassName($definition));
    }

    public function testClassNameWithTraits()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->types = array(
            new ReflectionClass('Eloquent\Phony\Test\TestTraitA'),
            new ReflectionClass('Eloquent\Phony\Test\TestTraitB'),
        );
        $definition = new MockDefinition($this->types);

        $this->assertSame('PhonyMock_TestTraitA_0', $this->subject->generateClassName($definition));
    }

    public function generateData()
    {
        $fixturePath = __DIR__ . '/../../../fixture/mock-generator';
        $data = array();

        foreach (scandir($fixturePath) as $testName) {
            if ('.' === $testName[0]) {
                continue;
            }

            $testName = $testName;
            $data[$testName] = array($testName);
        }

        return $data;
    }

    /**
     * @dataProvider generateData
     */
    public function testGenerate($testName)
    {
        if ($this->featureDetector->isSupported('object.constructor.php4')) {
            require_once __DIR__ . '/../../../src/TestClassOldConstructor.php';
        }

        $fixturePath = __DIR__ . '/../../../fixture/mock-generator';

        $detector = FeatureDetector::instance();
        $isSupported = require $fixturePath . '/' . $testName . '/supported.php';

        if (!$isSupported) {
            $this->markTestSkipped($message);
        }

        $builder = require $fixturePath . '/' . $testName . '/builder.php';
        $expected = file_get_contents($fixturePath . '/' . $testName . '/expected.php');
        $actual = $builder->source($this->subject);

        $this->assertSame($expected, "<?php\n\n" . $actual);

        eval($actual);

        $this->assertTrue(class_exists($builder->definition()->className()));
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
