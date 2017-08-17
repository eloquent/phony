<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MockGeneratorTest extends TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->signatureInspector = FunctionSignatureInspector::instance();
        $this->featureDetector = new FeatureDetector();
        $this->subject = new MockGenerator($this->labelSequencer, $this->signatureInspector, $this->featureDetector);
    }

    public function classNameData()
    {
        //                                      types                                    expected
        return [
            'Anonymous'                => [[],                                 'PhonyMock_0'],
            'Extends class'            => [['stdClass'],                       'PhonyMock_stdClass_0'],
            'Extends namespaced class' => [['Eloquent\Phony\Test\TestClassB'], 'PhonyMock_TestClassB_0'],
            'Inherits interface'       => [['Iterator', 'Countable'],          'PhonyMock_Iterator_0'],
        ];
    }

    /**
     * @dataProvider classNameData
     */
    public function testClassName($types, $expected)
    {
        $types = array_map(function ($type) { return new ReflectionClass($type); }, $types);
        $definition = new MockDefinition(
            $types,
            [],
            [],
            [],
            [],
            [],
            null
        );

        $this->assertSame($expected, $this->subject->generateClassName($definition));
    }

    public function testClassNameWithTraits()
    {
        $this->types = [
            new ReflectionClass('Eloquent\Phony\Test\TestTraitA'),
            new ReflectionClass('Eloquent\Phony\Test\TestTraitB'),
        ];
        $definition = new MockDefinition(
            $this->types,
            [],
            [],
            [],
            [],
            [],
            null
        );

        $this->assertSame('PhonyMock_TestTraitA_0', $this->subject->generateClassName($definition));
    }

    public function generateData()
    {
        $fixturePath = __DIR__ . '/../../fixture/mock-generator';
        $data = [];

        foreach (scandir($fixturePath) as $testName) {
            if ('.' === $testName[0]) {
                continue;
            }

            $data[$testName] = [$testName];
        }

        return $data;
    }

    /**
     * @dataProvider generateData
     */
    public function testGenerate($testName)
    {
        $fixturePath = __DIR__ . '/../../fixture/mock-generator';

        $detector = FeatureDetector::instance();
        $isSupported = require $fixturePath . '/' . $testName . '/supported.php';

        if (!$isSupported) {
            $this->markTestSkipped($message);
        }

        $factory = MockBuilderFactory::instance();
        $builder = require $fixturePath . '/' . $testName . '/builder.php';
        $expected = file_get_contents($fixturePath . '/' . $testName . '/expected.php');
        $expected = str_replace("\n", PHP_EOL, $expected);
        $actual = $builder->source($this->subject);

        $this->assertSame($expected, '<?php' . PHP_EOL . PHP_EOL . $actual);

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
