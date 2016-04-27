<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class EmptyValueFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new EmptyValueFactory();
        $this->subject->setMockBuilderFactory(MockBuilderFactory::instance());
    }

    public function fromTypeData()
    {
        return array(
            'bool'   => array('bool',   false),
            'int'    => array('int',    0),
            'float'  => array('float',  .0),
            'string' => array('string', ''),
            'array'  => array('array',  array()),
        );
    }

    /**
     * @dataProvider fromTypeData
     */
    public function testFromType($type, $expected)
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $reflector = new ReflectionFunction(eval("return function () : $type {};"));
        $type = $reflector->getReturnType();

        $this->assertSame($expected, $this->subject->fromType($type));
    }

    public function testFromTypeWithCallable()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $reflector = new ReflectionFunction(eval('return function () : callable {};'));
        $type = $reflector->getReturnType();

        $this->assertInstanceOf('Closure', $this->subject->fromType($type));
    }

    public function testFromTypeWithStdClass()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $reflector = new ReflectionFunction(eval('return function () : stdClass {};'));
        $type = $reflector->getReturnType();

        $this->assertInstanceOf('stdClass', $this->subject->fromType($type));
    }

    public function testFromTypeWithTraversable()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $reflector = new ReflectionFunction(eval('return function () : Traversable {};'));
        $type = $reflector->getReturnType();

        $this->assertInstanceOf('EmptyIterator', $this->subject->fromType($type));
    }

    public function testFromTypeWithIterator()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $reflector = new ReflectionFunction(eval('return function () : Iterator {};'));
        $type = $reflector->getReturnType();

        $this->assertInstanceOf('EmptyIterator', $this->subject->fromType($type));
    }

    public function testFromTypeWithGenerator()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }
        if (!class_exists('Generator')) {
            $this->markTestSkipped('Requires generators.');
        }

        $reflector = new ReflectionFunction(eval('return function () : Generator {};'));
        $type = $reflector->getReturnType();
        $actual = $this->subject->fromType($type);

        $this->assertInstanceOf('Generator', $actual);
        $this->assertSame(array(), iterator_to_array($actual));
    }

    public function testFromTypeWithObject()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $reflector = new ReflectionFunction(eval('return function () : Eloquent\Phony\Test\TestClassA {};'));
        $type = $reflector->getReturnType();

        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $this->subject->fromType($type));
    }

    public function testFromTypeWithNullableType()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $reflector = new ReflectionFunction(eval('return function (int $i = null) {};'));
        $parameters = $reflector->getParameters();
        $type = $parameters[0]->getType();

        $this->assertNull($this->subject->fromType($type));
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
