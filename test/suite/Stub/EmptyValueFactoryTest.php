<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Reflection\FeatureDetector;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class EmptyValueFactoryTest extends TestCase
{
    protected function setUp()
    {
        if (!class_exists('ReflectionType')) {
            $this->markTestSkipped('Requires reflection types.');
        }

        $this->featureDetector = FeatureDetector::instance();
        $this->subject = new EmptyValueFactory($this->featureDetector);
        $this->subject->setStubVerifierFactory(StubVerifierFactory::instance());
        $this->subject->setMockBuilderFactory(MockBuilderFactory::instance());
    }

    private function createType($type)
    {
        $reflector = new ReflectionFunction(eval("return function (): $type {};"));

        return $reflector->getReturnType();
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
        $this->assertSame($expected, $this->subject->fromType($this->createType($type)));
    }

    public function testFromTypeWithStdClass()
    {
        $actual = $this->subject->fromType($this->createType('stdClass'));

        $this->assertSame(array(), (array) $actual);
        $this->assertSame('{}', json_encode($actual));
    }

    public function testFromTypeWithObject()
    {
        $actual = $this->subject->fromType($this->createType('object'));

        if ($this->featureDetector->isSupported('type.object')) {
            $this->assertSame(array(), (array) $actual);
            $this->assertSame('{}', json_encode($actual));
        } else {
            $this->assertInstanceOf('object', $actual);
            $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        }
    }

    public function testFromTypeWithCallable()
    {
        $actual = $this->subject->fromType($this->createType('callable'));

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertNull($actual());
    }

    public function testFromTypeWithClosure()
    {
        $actual = $this->subject->fromType($this->createType('Closure'));

        $this->assertInstanceOf('Closure', $actual);
        $this->assertNull($actual());
    }

    public function testFromTypeWithIterable()
    {
        if (!$this->featureDetector->isSupported('type.iterable')) {
            $this->markTestSkipped('Requires the iterable type.');
        }

        $this->assertSame(array(), $this->subject->fromType($this->createType('iterable')));
    }

    public function testFromTypeWithVoid()
    {
        if (!$this->featureDetector->isSupported('type.void')) {
            $this->markTestSkipped('Requires the void type.');
        }

        $this->assertNull($this->subject->fromType($this->createType('void')));
    }

    public function testFromTypeWithNullable()
    {
        if (!$this->featureDetector->isSupported('type.nullable')) {
            $this->markTestSkipped('Requires nullable types.');
        }

        $this->assertNull($this->subject->fromType($this->createType('?int')));
        $this->assertNull($this->subject->fromType($this->createType('?stdClass')));
    }

    public function fromTypeWithIteratorTypeData()
    {
        $types = array(
            'AppendIterator',
            'ArrayIterator',
            'CachingIterator',
            'CallbackFilterIterator',
            'DirectoryIterator',
            'DirectoryIterator',
            'EmptyIterator',
            'FilesystemIterator',
            'FilesystemIterator',
            'FilterIterator',
            'GlobIterator',
            'GlobIterator',
            'InfiniteIterator',
            'Iterator',
            'IteratorIterator',
            'LimitIterator',
            'MultipleIterator',
            'NoRewindIterator',
            'OuterIterator',
            'ParentIterator',
            'RecursiveArrayIterator',
            'RecursiveCachingIterator',
            'RecursiveCallbackFilterIterator',
            'RecursiveDirectoryIterator',
            'RecursiveDirectoryIterator',
            'RecursiveFilterIterator',
            'RecursiveIterator',
            'RecursiveIteratorIterator',
            'RecursiveRegexIterator',
            'RecursiveTreeIterator',
            'RegexIterator',
            'SeekableIterator',
            'Traversable',
        );
        $data = array();

        foreach ($types as $type) {
            $data[$type] = array($type);
        }

        return $data;
    }

    /**
     * @dataProvider fromTypeWithIteratorTypeData
     */
    public function testFromTypeWithIteratorType($type)
    {
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertSame(array(), iterator_to_array($actual));
    }

    public function testFromTypeWithGenerator()
    {
        if (!class_exists('Generator')) {
            $this->markTestSkipped('Requires generators.');
        }

        $actual = $this->subject->fromType($this->createType('Generator'));

        $this->assertInstanceOf('Generator', $actual);
        $this->assertSame(array(), iterator_to_array($actual));
    }

    public function fromTypeWithThrowableTypeData()
    {
        $types = array(
            'ArithmeticError',
            'AssertionError',
            'BadFunctionCallException',
            'BadMethodCallException',
            'DivisionByZeroError',
            'DomainException',
            'Error',
            'ErrorException',
            'Exception',
            'InvalidArgumentException',
            'LengthException',
            'LogicException',
            'OutOfBoundsException',
            'OutOfRangeException',
            'OverflowException',
            'ParseError',
            'PharException',
            'PDOException',
            'RangeException',
            'ReflectionException',
            'RuntimeException',
            'Throwable',
            'TypeError',
            'UnderflowException',
            'UnexpectedValueException',
        );
        $data = array();

        foreach ($types as $type) {
            $data[$type] = array($type);
        }

        return $data;
    }

    /**
     * @dataProvider fromTypeWithThrowableTypeData
     */
    public function testFromTypeWithThrowableType($type)
    {
        if (!class_exists($type) && !interface_exists($type)) {
            $this->markTestSkipped("Requires $type.");
        }

        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertSame('', (string) $actual->getMessage());
        $this->assertSame(0, (int) $actual->getCode());
        $this->assertNull($actual->getPrevious());
    }

    public function fromTypeWithCollectionTypeData()
    {
        $types = array(
            'SplDoublyLinkedList',
            'SplFixedArray',
            'SplHeap',
            'SplMaxHeap',
            'SplMinHeap',
            'SplObjectStorage',
            'SplPriorityQueue',
            'SplQueue',
            'SplStack',
        );
        $data = array();

        foreach ($types as $type) {
            $data[$type] = array($type);
        }

        return $data;
    }

    /**
     * @dataProvider fromTypeWithCollectionTypeData
     */
    public function testFromTypeWithCollectionType($type)
    {
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertSame(array(), iterator_to_array($actual));
        $this->assertSame(0, count($actual));
    }

    public function testFromTypeWithArrayAccess()
    {
        $type = 'ArrayAccess';
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertFalse(isset($actual[0]));
    }

    public function testFromTypeWithCountable()
    {
        $type = 'Countable';
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
        $this->assertSame(0, count($actual));
    }

    public function testFromTypeWithClass()
    {
        $type = 'Eloquent\Phony\Test\TestClassA';
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $actual);
    }

    public function testFromTypeWithNullableType()
    {
        $reflector = new ReflectionFunction(eval('return function (int $i = null) {};'));
        $parameters = $reflector->getParameters();
        $type = $parameters[0]->getType();

        $this->assertNull($this->subject->fromType($type));
    }

    public function testFromFunctionWithClassType()
    {
        $function = new ReflectionMethod('Eloquent\Phony\Test\TestInterfaceWithReturnType', 'classType');

        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $this->subject->fromFunction($function));
    }

    public function testFromFunctionWithScalarType()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('Requires non-HHVM runtime.');
        }

        $function = new ReflectionMethod('Eloquent\Phony\Test\TestInterfaceWithReturnType', 'scalarType');

        $this->assertSame(0, $this->subject->fromFunction($function));
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
