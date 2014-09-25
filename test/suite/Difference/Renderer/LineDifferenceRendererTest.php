<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Difference\Renderer;

use Eloquent\Phony\Difference\DifferenceEngine;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class LineDifferenceRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new LineDifferenceRenderer();

        $this->engine = new DifferenceEngine();
    }

    public function testConstructor()
    {
        $this->endOfLine = "\n";
        $this->contextSize = 2;
        $this->subject = new LineDifferenceRenderer($this->endOfLine, $this->contextSize);

        $this->assertSame($this->endOfLine, $this->subject->endOfLine());
        $this->assertSame($this->contextSize, $this->subject->contextSize());
    }

    public function testConstructorDefaults()
    {
        $this->assertSame("\n", $this->subject->endOfLine());
        $this->assertSame(3, $this->subject->contextSize());
    }

    public function renderLineDifferenceData()
    {
        $fixturePath = __DIR__ . '/../../../fixture/difference/line';

        $data = array();
        foreach (scandir($fixturePath) as $testName) {
            if ('.' === $testName[0]) {
                continue;
            }

            $data[$testName] = array($testName);
        }

        return $data;
    }

    /**
     * @dataProvider renderLineDifferenceData
     */
    public function testRenderLineDifference($testName)
    {
        $fixturePath = __DIR__ . '/../../../fixture/difference/line';
        $fromLabel = null;
        $toLabel = null;
        $contextSize = null;
        require $fixturePath . '/' . $testName . '/options.php';
        $from = file_get_contents($fixturePath . '/' . $testName . '/from');
        $to = file_get_contents($fixturePath . '/' . $testName . '/to');
        $expected = file_get_contents($fixturePath . '/' . $testName . '/diff');
        $actual = $this->subject->renderLineDifference(
            $this->engine->lineDifference($from, $to),
            $fromLabel,
            $toLabel,
            $contextSize
        );

        // echo "\n" .$expected, "\n" .$actual;

        $this->assertSame($expected, $actual);
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
