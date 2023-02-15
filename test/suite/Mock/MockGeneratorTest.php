<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock;

use AllowDynamicProperties;
use Countable;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
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
        $this->labelSequencer = new Sequencer();
        $this->signatureInspector = FunctionSignatureInspector::instance();
        $this->subject = new MockGenerator($this->labelSequencer, $this->signatureInspector);
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
            ''
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
            ''
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

        if (is_file($fixturePath . '/' . $testName . '/supported.php')) {
            $isSupported = require $fixturePath . '/' . $testName . '/supported.php';

            if (!$isSupported) {
                $this->markTestSkipped($message);
            }
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
