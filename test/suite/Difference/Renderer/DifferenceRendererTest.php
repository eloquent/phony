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

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class DifferenceRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new DifferenceRenderer;
    }

    public function testConstructor()
    {
        $this->endOfLine = "\n";
        $this->contextSize = 2;
        $this->subject = new DifferenceRenderer($this->endOfLine, $this->contextSize);

        $this->assertSame($this->endOfLine, $this->subject->endOfLine());
        $this->assertSame($this->contextSize, $this->subject->contextSize());
    }

    public function testConstructorDefaults()
    {
        $this->assertSame("\n", $this->subject->endOfLine());
        $this->assertSame(3, $this->subject->contextSize());
    }

    // public function testRenderLineDifference()
    // {
    //     $difference = array(
    //         array(' ', "a\n"),
    //         array(' ', "b\n"),
    //         array('-', "c\n"),
    //         array('-', "d\n"),
    //         array('+', "e\n"),
    //         array(' ', "f\n"),
    //         array(' ', "g\n"),
    //         array('-', "h\n"),
    //         array('+', "i\n"),
    //         array(' ', "j\n"),
    //         array(' ', "k\n"),
    //         array(' ', "l\n"),
    //         array('-', "m\n"),
    //         array('+', "n\n"),
    //         array('+', "o\n"),
    //         array(' ', "p\n"),
    //     );
    //     $expected = "--- fromLabel\n" .
    //         "+++ toLabel\n" .
    //         // "@@ -2,7 +2,6 @@\n" .
    //         " b\n" .
    //         "-c\n" .
    //         "-d\n" .
    //         "+e\n" .
    //         " f\n" .
    //         " g\n" .
    //         "-h\n" .
    //         "+i\n" .
    //         " j\n" .
    //         // "@@ -10,3 +9,4 @@\n" .
    //         " l\n" .
    //         "-m\n" .
    //         "+n\n" .
    //         "+o\n" .
    //         " p\n";

    //     $this->assertSame($expected, $this->subject->renderLineDifference($difference, 'fromLabel', 'toLabel', 1));
    // }

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
