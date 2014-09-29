<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Renderer;

use Eloquent\Phony\Call\Call;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use SebastianBergmann\Exporter\Exporter;

class CallRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->exporter = new Exporter();
        $this->subject = new CallRenderer($this->exporter);
    }

    public function testConstructor()
    {
        $this->assertSame($this->exporter, $this->subject->exporter());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallRenderer();

        $this->assertEquals($this->exporter, $this->subject->exporter());
    }

    public function renderData()
    {
        return array(
            'Method' => array(
                new Call(new ReflectionMethod(__METHOD__), array(), null, 0, .1, .2),
                "Eloquent\Phony\Call\Renderer\CallRendererTest->renderData()",
            ),
            'Static method' => array(
                new Call(new ReflectionMethod('ReflectionMethod::export'), array(), null, 0, .1, .2),
                "ReflectionMethod::export()",
            ),
            'Function' => array(
                new Call(new ReflectionFunction('function_exists'), array(), null, 0, .1, .2),
                "function_exists()",
            ),
            'Closure' => array(
                new Call(new ReflectionFunction(function () {}), array(), null, 0, .1, .2),
                "Eloquent\Phony\Call\Renderer\{closure}()",
            ),
            'With arguments' => array(
                new Call(new ReflectionFunction('function_exists'), array('argument', 111), null, 0, .1, .2),
                "function_exists('argument', 111)",
            ),
        );
    }

    /**
     * @dataProvider renderData
     */
    public function testRender($call, $expected)
    {
        $this->assertSame($expected, $this->subject->render($call));
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
