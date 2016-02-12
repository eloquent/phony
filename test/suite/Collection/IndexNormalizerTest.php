<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Collection;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class IndexNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new IndexNormalizer();
    }

    public function normalizeData()
    {
        //                            count index normalized
        return array(
            'Zero'           => array(3,    0,    0),
            'Positive'       => array(3,    1,    1),
            'Negative last'  => array(3,    -1,   2),
            'Negative other' => array(3,    -2,   1),
        );
    }

    public function normalizeFailureData()
    {
        //                                   count index
        return array(
            'Zero when empty'       => array(0,    0),
            'Positive'              => array(1,    1),
            'Negative'              => array(0,    -1),
            'Negative beyond start' => array(1,    -2),
        );
    }

    /**
     * @dataProvider normalizeData
     */
    public function testNormalize($size, $index, $expected)
    {
        $this->assertSame($expected, $this->subject->normalize($size, $index));
    }

    /**
     * @dataProvider normalizeFailureData
     */
    public function testNormalizeFailure($size, $index)
    {
        $this->setExpectedException('Eloquent\Phony\Collection\Exception\UndefinedIndexException');
        $this->subject->normalize($size, $index);
    }

    /**
     * @dataProvider normalizeData
     */
    public function testTryNormalize($size, $index, $expected)
    {
        $normalized = -1;

        $this->assertTrue($this->subject->tryNormalize($size, $index, $normalized));
        $this->assertSame($expected, $normalized);
    }

    /**
     * @dataProvider normalizeFailureData
     */
    public function testTryNormalizeFailure($size, $index)
    {
        $normalized = -1;

        $this->assertFalse($this->subject->tryNormalize($size, $index, $normalized));
        $this->assertNull($normalized);
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
