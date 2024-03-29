<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock;

use AllowDynamicProperties;
use Countable;
use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestTraitA;
use Eloquent\Phony\Test\TestTraitB;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

#[AllowDynamicProperties]
class MockGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer(mockClassLabelSequence: new Sequencer());
        $this->subject = $this->container->mockGenerator;
    }

    public function classNameData()
    {
        //                                 types                                expected
        return [
            'Anonymous'                => [[],                                  'PhonyMock_0'],
            'Extends class'            => [[stdClass::class],                   'PhonyMock_stdClass_0'],
            'Extends namespaced class' => [[TestClassB::class],                 'PhonyMock_TestClassB_0'],
            'Inherits interface'       => [[Iterator::class, Countable::class], 'PhonyMock_Iterator_0'],
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
            '',
            new FeatureDetector()
        );

        $this->assertSame($expected, $this->subject->generateClassName($definition));
    }

    public function testClassNameWithTraits()
    {
        $this->types = [
            new ReflectionClass(TestTraitA::class),
            new ReflectionClass(TestTraitB::class),
        ];
        $definition = new MockDefinition(
            $this->types,
            [],
            [],
            [],
            [],
            [],
            '',
            new FeatureDetector()
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
     * @requires PHP >= 8.1
     * @dataProvider generateData
     */
    public function testGenerate($testName)
    {
        $fixturePath = __DIR__ . '/../../fixture/mock-generator';

        if (is_file($fixturePath . '/' . $testName . '/supported.php')) {
            $isSupported = require $fixturePath . '/' . $testName . '/supported.php';

            if (!$isSupported) {
                $this->markTestSkipped($message);
            }
        }

        $factory = $this->container->mockBuilderFactory;
        $builder = require $fixturePath . '/' . $testName . '/builder.php';
        $expected = file_get_contents($fixturePath . '/' . $testName . '/expected.php');
        $expected = str_replace("\n", PHP_EOL, $expected);
        $actual = $builder->source($this->subject);

        $this->assertSame($expected, '<?php' . PHP_EOL . PHP_EOL . $actual);

        eval($actual);

        $this->assertTrue(class_exists($builder->definition()->className()));
    }
}
