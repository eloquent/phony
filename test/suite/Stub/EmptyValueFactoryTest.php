<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use AppendIterator;
use ArithmeticError;
use ArrayAccess;
use ArrayIterator;
use AssertionError;
use BadFunctionCallException;
use BadMethodCallException;
use CachingIterator;
use CallbackFilterIterator;
use Closure;
use Countable;
use DirectoryIterator;
use DivisionByZeroError;
use DomainException;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestInterfaceWithReturnType;
use EmptyIterator;
use Error;
use ErrorException;
use Exception;
use FilesystemIterator;
use FilterIterator;
use Generator;
use GlobIterator;
use InfiniteIterator;
use InvalidArgumentException;
use Iterator;
use IteratorIterator;
use LengthException;
use LimitIterator;
use LogicException;
use MultipleIterator;
use NoRewindIterator;
use OuterIterator;
use OutOfBoundsException;
use OutOfRangeException;
use OverflowException;
use ParentIterator;
use ParseError;
use PDOException;
use PharException;
use PHPUnit\Framework\TestCase;
use RangeException;
use RecursiveArrayIterator;
use RecursiveCachingIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RecursiveTreeIterator;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use RegexIterator;
use RuntimeException;
use SeekableIterator;
use SplDoublyLinkedList;
use SplFixedArray;
use SplHeap;
use SplMaxHeap;
use SplMinHeap;
use SplObjectStorage;
use SplPriorityQueue;
use SplQueue;
use SplStack;
use stdClass;
use Throwable;
use Traversable;
use TypeError;
use UnderflowException;
use UnexpectedValueException;

class EmptyValueFactoryTest extends TestCase
{
    protected function setUp(): void
    {
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
        return [
            'bool'   => ['bool',   false],
            'int'    => ['int',    0],
            'float'  => ['float',  .0],
            'string' => ['string', ''],
            'array'  => ['array',  []],
        ];
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
        $actual = $this->subject->fromType($this->createType(stdClass::class));

        $this->assertSame([], (array) $actual);
        $this->assertSame('{}', json_encode($actual));
    }

    public function testFromTypeWithObject()
    {
        $actual = $this->subject->fromType($this->createType('object'));

        $this->assertSame([], (array) $actual);
        $this->assertSame('{}', json_encode($actual));
    }

    public function testFromTypeWithCallable()
    {
        $actual = $this->subject->fromType($this->createType('callable'));

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertNull($actual());
    }

    public function testFromTypeWithClosure()
    {
        $actual = $this->subject->fromType($this->createType(Closure::class));

        $this->assertInstanceOf(Closure::class, $actual);
        $this->assertNull($actual());
    }

    public function testFromTypeWithIterable()
    {
        $this->assertSame([], $this->subject->fromType($this->createType('iterable')));
    }

    public function testFromTypeWithVoid()
    {
        $this->assertNull($this->subject->fromType($this->createType('void')));
    }

    public function testFromTypeWithNullable()
    {
        $this->assertNull($this->subject->fromType($this->createType('?int')));
        $this->assertNull($this->subject->fromType($this->createType('?stdClass')));
    }

    public function fromTypeWithIteratorTypeData()
    {
        $types = [
            AppendIterator::class,
            ArrayIterator::class,
            CachingIterator::class,
            CallbackFilterIterator::class,
            DirectoryIterator::class,
            EmptyIterator::class,
            FilesystemIterator::class,
            FilterIterator::class,
            GlobIterator::class,
            InfiniteIterator::class,
            Iterator::class,
            IteratorIterator::class,
            LimitIterator::class,
            MultipleIterator::class,
            NoRewindIterator::class,
            OuterIterator::class,
            ParentIterator::class,
            RecursiveArrayIterator::class,
            RecursiveCachingIterator::class,
            RecursiveCallbackFilterIterator::class,
            RecursiveDirectoryIterator::class,
            RecursiveFilterIterator::class,
            RecursiveIterator::class,
            RecursiveIteratorIterator::class,
            RecursiveRegexIterator::class,
            RecursiveTreeIterator::class,
            RegexIterator::class,
            SeekableIterator::class,
            Traversable::class,
        ];
        $data = [];

        foreach ($types as $type) {
            $data[$type] = [$type];
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
        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertSame([], iterator_to_array($actual));
    }

    public function testFromTypeWithGenerator()
    {
        $actual = $this->subject->fromType($this->createType(Generator::class));

        $this->assertInstanceOf(Generator::class, $actual);
        $this->assertSame([], iterator_to_array($actual));
    }

    public function fromTypeWithThrowableTypeData()
    {
        $types = [
            ArithmeticError::class,
            AssertionError::class,
            BadFunctionCallException::class,
            BadMethodCallException::class,
            DivisionByZeroError::class,
            DomainException::class,
            Error::class,
            ErrorException::class,
            Exception::class,
            InvalidArgumentException::class,
            LengthException::class,
            LogicException::class,
            OutOfBoundsException::class,
            OutOfRangeException::class,
            OverflowException::class,
            ParseError::class,
            PharException::class,
            PDOException::class,
            RangeException::class,
            ReflectionException::class,
            RuntimeException::class,
            Throwable::class,
            TypeError::class,
            UnderflowException::class,
            UnexpectedValueException::class,
        ];
        $data = [];

        foreach ($types as $type) {
            $data[$type] = [$type];
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
        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertSame('', (string) $actual->getMessage());
        $this->assertSame(0, (int) $actual->getCode());
        $this->assertNull($actual->getPrevious());
    }

    public function fromTypeWithCollectionTypeData()
    {
        $types = [
            SplDoublyLinkedList::class,
            SplFixedArray::class,
            SplHeap::class,
            SplMaxHeap::class,
            SplMinHeap::class,
            SplObjectStorage::class,
            SplPriorityQueue::class,
            SplQueue::class,
            SplStack::class,
        ];
        $data = [];

        foreach ($types as $type) {
            $data[$type] = [$type];
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
        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertSame([], iterator_to_array($actual));
        $this->assertCount(0, $actual);
    }

    public function testFromTypeWithArrayAccess()
    {
        $type = ArrayAccess::class;
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertFalse(isset($actual[0]));
    }

    public function testFromTypeWithCountable()
    {
        $type = Countable::class;
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf(Mock::class, $actual);
        $this->assertCount(0, $actual);
    }

    public function testFromTypeWithClass()
    {
        $type = TestClassA::class;
        $actual = $this->subject->fromType($this->createType($type));

        $this->assertInstanceOf($type, $actual);
        $this->assertInstanceOf(Mock::class, $actual);
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
        $function = new ReflectionMethod(TestInterfaceWithReturnType::class, 'classType');

        $this->assertInstanceOf(TestClassA::class, $this->subject->fromFunction($function));
    }

    public function testFromFunctionWithScalarType()
    {
        $function = new ReflectionMethod(TestInterfaceWithReturnType::class, 'scalarType');

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
