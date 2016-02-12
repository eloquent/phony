<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Exporter;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Test\Properties\TestDerivedClassA;
use Eloquent\Phony\Test\TestClassE;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;
use TestClass;

class InlineExporterTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->depth = -1;
        $this->subject = new InlineExporter($this->depth);

        $this->featureDetector = FeatureDetector::instance();
    }

    public function testSetDepth()
    {
        $this->assertSame($this->depth, $this->subject->setDepth(111));
        $this->assertSame(111, $this->subject->setDepth($this->depth));
    }

    public function exportData()
    {
        return array(
            'null'             => array(null,                                            'null'),
            'true'             => array(true,                                            'true'),
            'false'            => array(false,                                           'false'),
            '0'                => array(0,                                               '0'),
            '-0'               => array(-0,                                              '0'),
            '1'                => array(1,                                               '1'),
            '-1'               => array(-1,                                              '-1'),
            '0.0'              => array(0.0,                                             '0.000000e+0'),
            '-0.0'             => array(-0.0,                                            '0.000000e+0'),
            '1.0'              => array(1.0,                                             '1.000000e+0'),
            '-1.0'             => array(-1.0,                                            '-1.000000e+0'),
            'STDIN'            => array(STDIN,                                           'resource#1'),
            'STDOUT'           => array(STDOUT,                                          'resource#2'),
            'a\nb'             => array("a\nb",                                          '"a\nb"'),
            '[]'               => array(array(),                                         '#0[]'),
            '[1]'              => array(array(1),                                        '#0[1]'),
            '[1, 1]'           => array(array(1, 1),                                     '#0[1, 1]'),
            '[1: 1]'           => array(array(1 => 1),                                   '#0[1: 1]'),
            '[1: 1, 2: 2]'     => array(array(1 => 1, 2 => 2),                           '#0[1: 1, 2: 2]'),
            '[1, [1, 1]]'      => array(array(1, array(1, 1)),                           '#0[1, #1[1, 1]]'),
            '[[1, 1], [1, 1]]' => array(array(array(1, 1), array(1, 1)),                 '#0[#1[1, 1], #2[1, 1]]'),
            '{a: 0}'           => array((object) array('a' => 0),                        '#0{a: 0}'),
            '{a: 0, b: 1}'     => array((object) array('a' => 0, 'b' => 1),              '#0{a: 0, b: 1}'),
            '{a: {a: 0}}'      => array((object) array('a' => (object) array('a' => 0)), '#0{a: #1{a: 0}}'),
            '{a: []}'          => array((object) array('a' => array()),                  '#0{a: #0[]}'),
            'object'           => array(new TestClass(),                                 'TestClass#0{}'),
        );
    }

    /**
     * @dataProvider exportData
     */
    public function testExport($value, $expected)
    {
        $copy = $value;

        $this->assertSame($expected, $this->subject->export($value));
        $this->assertSame($copy, $value);
    }

    public function testExportMaxDepthWithArrays()
    {
        $array = array();
        $value = array(&$array, array(&$array));

        $this->assertSame('#0[:2]', $this->subject->export($value, 0));
        $this->assertSame('#0[#1[], #2[:1]]', $this->subject->export($value, 1));
        $this->assertSame('#0[#1[], #2[#1[]]]', $this->subject->export($value, 2));
        $this->assertSame('#0[#1[], #2[#1[]]]', $this->subject->export($value));
    }

    public function testExportMaxDepthWithObjects()
    {
        $object = (object) array();
        $value = (object) array('a' => &$object, 'b' => (object) array('a' => &$object));

        $this->assertSame('#0{:2}', $this->subject->export($value, 0));
        $this->assertSame('#0{a: #1{}, b: #2{:1}}', $this->subject->export($value, 1));
        $this->assertSame('#0{a: #1{}, b: #2{a: #1{}}}', $this->subject->export($value, 2));
        $this->assertSame('#0{a: #1{}, b: #2{a: #1{}}}', $this->subject->export($value));
        $this->assertSame('#1{}', $this->subject->export($object, 0));
    }

    public function testExportRecursiveObject()
    {
        $value = new TestClass();
        $value->inner = $value;

        $this->assertSame('TestClass#0{inner: #0{}}', $this->subject->export($value));
    }

    public function testExportRecursiveArray()
    {
        $value = array();
        $value['inner'] = &$value;

        $this->assertSame('#0["inner": #0[]]', $this->subject->export($value));
    }

    public function testExportObjectPersistentIds()
    {
        $objectA = (object) array();
        $objectB = (object) array();

        $this->assertSame('#0{}', $this->subject->export($objectA));
        $this->assertSame('#1{}', $this->subject->export($objectB));
        $this->assertSame('#0{}', $this->subject->export($objectA));
    }

    public function testExportWithoutIncrementingIds()
    {
        $this->subject = new InlineExporter($this->depth, false);
        $value = array(array(), (object) array(), (object) array());

        $this->assertSame('#0[#0[], #0{}, #0{}]', $this->subject->export($value));
    }

    public function testExportInaccessibleProperties()
    {
        $value = new TestClassE();

        $this->assertSame(
            'Eloquent\Phony\Test\TestClassE#0{privateProperty: "private"}',
            $this->subject->export($value)
        );
    }

    public function testExportInaccessibleIneritedProperties()
    {
        $value = new TestDerivedClassA();

        if (
            !$this->featureDetector->isSupported('runtime.hhvm') &&
            version_compare(PHP_VERSION, '5.4.x', '>=')
        ) {
            $expected = 'Eloquent\Phony\Test\Properties\TestDerivedClassA#0{' .
                'derivedPublic: "<derived-public>", ' .
                'derivedPrivate: "<derived-private>", ' .
                'basePrivate: "<derived-base-private>", ' .
                'derivedProtected: "<derived-protected>", ' .
                'basePublic: "<base-public>", ' .
                'baseProtected: "<base-protected>", ' .
                'Eloquent\Phony\Test\Properties\TestBaseClass.basePrivate: "<base-private>"}';
        } else {
            $expected = 'Eloquent\Phony\Test\Properties\TestDerivedClassA#0{' .
                'derivedPublic: "<derived-public>", ' .
                'derivedPrivate: "<derived-private>", ' .
                'basePrivate: "<derived-base-private>", ' .
                'derivedProtected: "<derived-protected>", ' .
                'basePublic: "<base-public>", ' .
                'Eloquent\Phony\Test\Properties\TestBaseClass.basePrivate: "<base-private>", ' .
                'baseProtected: "<base-protected>"}';
        }

        $this->assertSame($expected, $this->subject->export($value));
    }

    public function testExportMocks()
    {
        $builder = MockBuilderFactory::instance()->create('Eloquent\Phony\Test\Properties\TestBaseClass')
            ->named('PhonyMockInlineExporterExportMocks');
        $mock = $builder->get();
        Phony::on($mock)->setLabel('label');

        $this->assertSame(
            'PhonyMockInlineExporterExportMocks#0{basePublic: "<base-public>", basePrivate: "<base-private>", ' .
                'baseProtected: "<base-protected>", phony.label: "label"}',
            $this->subject->export($mock)
        );
    }

    public function testExportExceptions()
    {
        $exceptionA = new RuntimeException();
        $exceptionB = new RuntimeException('message');
        $exceptionC = new RuntimeException('message', 111);
        $exceptionD = new RuntimeException('message', 111, $exceptionA);
        $exceptionE = new RuntimeException('message', 111, $exceptionA);
        $exceptionE->arbitrary = 'yolo';

        $this->assertSame('RuntimeException#0{}', $this->subject->export($exceptionA));
        $this->assertSame('RuntimeException#1{message: "message"}', $this->subject->export($exceptionB));
        $this->assertSame('RuntimeException#2{message: "message", code: 111}', $this->subject->export($exceptionC));
        $this->assertSame(
            'RuntimeException#3{message: "message", code: 111, previous: RuntimeException#0{}}',
            $this->subject->export($exceptionD)
        );
        $this->assertSame(
            'RuntimeException#4{message: "message", code: 111, previous: RuntimeException#0{}, arbitrary: "yolo"}',
            $this->subject->export($exceptionE)
        );
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
