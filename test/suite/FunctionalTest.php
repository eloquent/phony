<?php

use function Eloquent\Phony\anyOrder;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use function Eloquent\Phony\equalTo;
use Eloquent\Phony\Facade\Globals;
use function Eloquent\Phony\inOrder;
use function Eloquent\Phony\mock;
use Eloquent\Phony\Mock\Exception\AnonymousClassException;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Mock;
use function Eloquent\Phony\mockBuilder;
use function Eloquent\Phony\on;
use function Eloquent\Phony\onStatic;
use function Eloquent\Phony\partialMock;
use Eloquent\Phony\Phony;
use function Eloquent\Phony\restoreGlobalFunctions;
use function Eloquent\Phony\setUseColor;
use function Eloquent\Phony\spy;
use Eloquent\Phony\Spy\SpyVerifier;
use function Eloquent\Phony\spyGlobal;
use function Eloquent\Phony\stub;
use Eloquent\Phony\Stub\Exception\FinalReturnTypeException;
use Eloquent\Phony\Stub\Exception\UnusedStubCriteriaException;
use function Eloquent\Phony\stubGlobal;
use Eloquent\Phony\Test;
use Eloquent\Phony\Test\AbstractTestClassWithFinalReturnType;
use Eloquent\Phony\Test\Php81\TestBackedEnum;
use Eloquent\Phony\Test\Php81\TestBasicEnum;
use Eloquent\Phony\Test\Php81\TestClassWithReadonlyProperties;
use Eloquent\Phony\Test\Php81\TestInterfaceUsingEnums;
use Eloquent\Phony\Test\Php81\TestInterfaceWithIntersectionFinalReturnType;
use Eloquent\Phony\Test\Php81\TestInterfaceWithIntersectionTypes;
use Eloquent\Phony\Test\Php82\TestClassReadonly;
use Eloquent\Phony\Test\Php82\TestClassReadonlySubclass;
use Eloquent\Phony\Test\Php82\TestInterfaceWithDnfTypes;
use Eloquent\Phony\Test\Php82\TestInterfaceWithPhp82StandaloneTypes;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestClassC;
use Eloquent\Phony\Test\TestClassD;
use Eloquent\Phony\Test\TestClassG;
use Eloquent\Phony\Test\TestClassI;
use Eloquent\Phony\Test\TestClassWithConstructorProperties;
use Eloquent\Phony\Test\TestClassWithFinalReturnType;
use Eloquent\Phony\Test\TestClassWithSerializeMagicMethods;
use Eloquent\Phony\Test\TestClassWithToStringException;
use Eloquent\Phony\Test\TestClassWithVariadicNamedArguments;
use Eloquent\Phony\Test\TestCountableIterator;
use Eloquent\Phony\Test\TestFinalClassA;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestInterfaceC;
use Eloquent\Phony\Test\TestInterfaceD;
use Eloquent\Phony\Test\TestInterfaceE;
use Eloquent\Phony\Test\TestInterfaceWithFinalReturnType;
use Eloquent\Phony\Test\TestInterfaceWithReturnType;
use Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint;
use Eloquent\Phony\Test\TestInterfaceWithSelfReturnType;
use Eloquent\Phony\Test\TestInterfaceWithStaticReturnType;
use Eloquent\Phony\Test\TestInterfaceWithUnionFinalReturnType;
use Eloquent\Phony\Test\TestInterfaceWithUnionTypes;
use Eloquent\Phony\Test\TestInterfaceWithVariadicParameter;
use Eloquent\Phony\Test\TestInterfaceWithVariadicParameterByReference;
use Eloquent\Phony\Test\TestInterfaceWithVariadicParameterWithNullableType;
use Eloquent\Phony\Test\TestInterfaceWithVariadicParameterWithType;
use Eloquent\Phony\Test\TestInvocable;
use Eloquent\Phony\Test\TestTraitA;
use Eloquent\Phony\Test\TestTraitC;
use Eloquent\Phony\Test\TestTraitD;
use Eloquent\Phony\Test\TestTraitE;
use Eloquent\Phony\Test\TestTraitF;
use Eloquent\Phony\Test\TestTraitH;
use Eloquent\Phony\Test\TestTraitJ;
use Eloquent\Phony\Test\TestTraitWithSelfType;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class FunctionalTest extends TestCase
{
    protected function setUp(): void
    {
        $this->exporter = Globals::$container->exporter;

        setUseColor(false);
    }

    public function testMockingStatic()
    {
        $handle = Phony::partialMock(TestClassA::class);
        $handle->testClassAMethodA->with('a', 'b')->returns('x');
        $mock = $handle->get();

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));

        $this->assertSame(
            ['a', 'b'],
            $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->arguments()->all()
        );
        $this->assertSame('b', $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->argument(1));
    }

    public function testMockingFunctions()
    {
        $handle = partialMock(TestClassA::class);
        $handle->testClassAMethodA->with('a', 'b')->returns('x');
        $mock = $handle->get();

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));
        $this->assertSame(
            ['a', 'b'],
            $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->arguments()->all()
        );
        $this->assertSame('b', $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->argument(1));
    }

    public function testMockCalls()
    {
        $mock = partialMock(TestClassB::class, ['A', 'B'])->get();
        $e = 'e';
        $n = 'n';
        $q = 'q';
        $r = 'r';

        $this->assertSame(['A', 'B'], $mock->constructorArguments);
        $this->assertSame('ab', $mock::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('cde', $mock::testClassAStaticMethodB('c', 'd', $e));
        $this->assertSame('third', $e);
        $this->assertSame('fg', $mock::testClassBStaticMethodA('f', 'g'));
        $this->assertSame('hi', $mock::testClassBStaticMethodB('h', 'i'));
        $this->assertSame('jk', $mock->testClassAMethodA('j', 'k'));
        $this->assertSame('lmn', $mock->testClassAMethodB('l', 'm', $n));
        $this->assertSame('third', $n);
        $this->assertSame('op', $mock->testClassBMethodA('o', 'p'));
        $this->assertSame('qr', $mock->testClassBMethodB($q, $r));
        $this->assertSame('first', $q);
        $this->assertSame('second', $r);
    }

    public function testMagicMethodMocking()
    {
        $mock = partialMock(TestClassB::class)->get();

        $this->assertSame('static magic nonexistent ab', $mock::nonexistent('a', 'b'));
        $this->assertSame('magic nonexistent ab', $mock->nonexistent('a', 'b'));

        onStatic($mock)->nonexistent->with('c', 'd')->returns('x');
        on($mock)->nonexistent->with('c', 'd')->returns('z');

        $this->assertSame('x', $mock::nonexistent('c', 'd'));
        $this->assertSame('static magic nonexistent ef', $mock::nonexistent('e', 'f'));
        $this->assertSame('z', $mock->nonexistent('c', 'd'));
        $this->assertSame('magic nonexistent ef', $mock->nonexistent('e', 'f'));
    }

    public function testMockMocking()
    {
        $mock = partialMock();
        $mockMock = partialMock($mock->className());

        $this->assertInstanceOf(get_class($mock->get()), $mockMock->get());
        $this->assertNotInstanceOf(get_class($mockMock->get()), $mock->get());
    }

    public function testVariadicParameterMocking()
    {
        $handle = mock(TestInterfaceWithVariadicParameter::class);
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );

        $this->assertSame([1, 2], $handle->get()->method(1, 2));
    }

    public function testVariadicParameterMockingWithType()
    {
        $handle = mock(TestInterfaceWithVariadicParameterWithType::class);
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );
        $a = (object) [];
        $b = (object) [];

        $this->assertSame([$a, $b], $handle->get()->method($a, $b));
    }

    public function testVariadicParameterMockingWithNullableType()
    {
        $handle = mock(TestInterfaceWithVariadicParameterWithNullableType::class);
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );
        $a = (object) [];
        $b = null;
        $c = (object) [];

        $this->assertSame([$a, $b, $c], $handle->get()->method($a, $b, $c));
    }

    public function testVariadicParameterMockingByReference()
    {
        $handle = mock(TestInterfaceWithVariadicParameterByReference::class);
        $handle->method
            ->setsArgument(0, 'a')
            ->setsArgument(1, 'b')
            ->returns();
        $a = null;
        $b = null;
        $handle->get()->method($a, $b);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testScalarTypeHintMocking()
    {
        $handle = mock(TestInterfaceWithScalarTypeHint::class);
        $handle->get()->method(123, 1.23, '<string>', true);

        $this->assertTrue((bool) $handle->method->calledWith(123, 1.23, '<string>', true));
    }

    public function testReturnTypeMocking()
    {
        $handle = mock(TestInterfaceWithReturnType::class);
        $object = new TestClassA();
        $handle->classType->with('x')->does(
            function () use ($object) {
                return $object;
            }
        );
        $handle->scalarType->with('x')->does(
            function () {
                return 123;
            }
        );

        $this->assertSame($object, $handle->get()->classType('x'));
        $this->assertInstanceOf(TestClassA::class, $handle->get()->classType());
        $this->assertInstanceOf(Mock::class, $handle->get()->classType());
        $this->assertInstanceOf(InstanceHandle::class, on($handle->get()->classType()));
        $this->assertSame(123, $handle->get()->scalarType('x'));
        $this->assertSame(0, $handle->get()->scalarType());
    }

    public function testMagicMethodReturnTypeMocking()
    {
        $mock = mock(TestInterfaceWithReturnType::class)->get();

        onStatic($mock)->nonexistent->returns('x');
        on($mock)->nonexistent->returns('z');

        $this->assertSame('x', $mock::nonexistent());
        $this->assertSame('z', $mock->nonexistent());
    }

    public function testGeneratorReturnTypeSpying()
    {
        $stub = stub(function (): Generator {})->returns();
        iterator_to_array($stub());

        $this->assertTrue((bool) $stub->generated());
    }

    public function testReturnTypeMockingInvalidType()
    {
        $handle = mock(TestInterfaceWithReturnType::class);
        $handle->scalarType->returns('<string>');

        $this->expectException(TypeError::class);
        $handle->get()->scalarType();
    }

    public function testSpyStatic()
    {
        $spy = Phony::spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $this->assertTrue((bool) $spy->twice()->called());
        $this->assertTrue((bool) $spy->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $spy->calledWith('a', 'b', '~'));
        $this->assertTrue((bool) $spy->calledWith('a', '*'));
        $this->assertTrue((bool) $spy->calledWith('*'));
        $this->assertTrue((bool) $spy->calledWith(111));
        $this->assertTrue((bool) $spy->callAt(0)->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $spy->callAt(1)->calledWith(111));

        $this->assertTrue(
            (bool) Phony::inOrder(
                $spy->calledWith('a', 'b', 'c'),
                $spy->calledWith(111)
            )
        );
    }

    public function testSpyFunction()
    {
        $spy = spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $this->assertTrue((bool) $spy->twice()->called());
        $this->assertTrue((bool) $spy->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $spy->calledWith('a', 'b', '~'));
        $this->assertTrue((bool) $spy->calledWith('a', '*'));
        $this->assertTrue((bool) $spy->calledWith('*'));
        $this->assertTrue((bool) $spy->calledWith(111));
        $this->assertTrue((bool) $spy->callAt(0)->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $spy->callAt(1)->calledWith(111));

        $this->assertTrue(
            (bool) inOrder(
                $spy->calledWith('a', 'b', 'c'),
                $spy->calledWith(111)
            )
        );
    }

    public function testSpyReturnType()
    {
        $spy = spy(function (): int { return 123; });

        $this->assertSame(123, $spy());
    }

    public function testSpyArrowFunction()
    {
        $spy = spy(eval('return fn($x) => $x;'));

        $this->assertSame(111, $spy(111));
    }

    public function testSpyGlobal()
    {
        $stubA = spyGlobal('vsprintf', Test::class);

        $this->assertSame('a, b', Test\vsprintf('%s, %s', ['a', 'b']));
        $this->assertTrue((bool) $stubA->calledWith('%s, %s', ['a', 'b']));

        $stubB = spyGlobal('vsprintf', Test::class);

        $this->assertSame('a, b', Test\vsprintf('%s, %s', ['a', 'b']));
        $this->assertTrue((bool) $stubB->calledWith('%s, %s', ['a', 'b']));
    }

    public function testStubStatic()
    {
        $stub = Phony::stub()
            ->returns('x')
            ->with(111)->returns('y');

        $this->assertSame('x', $stub('a', 'b', 'c'));
        $this->assertSame('y', $stub(111));
        $this->assertTrue((bool) $stub->twice()->called());
        $this->assertTrue((bool) $stub->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $stub->calledWith('a', 'b', '~'));
        $this->assertTrue((bool) $stub->calledWith('a', '*'));
        $this->assertTrue((bool) $stub->calledWith('*'));
        $this->assertTrue((bool) $stub->calledWith(111));
        $this->assertTrue((bool) $stub->callAt(0)->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $stub->callAt(1)->calledWith(111));
        $this->assertTrue((bool) $stub->returned('x'));
        $this->assertTrue((bool) $stub->returned('y'));

        $this->assertTrue(
            (bool) Phony::inOrder(
                $stub->calledWith('a', 'b', 'c'),
                $stub->returned('x'),
                $stub->calledWith(111),
                $stub->returned('y')
            )
        );
    }

    public function testStubFunction()
    {
        $stub = stub()
            ->returns('x')
            ->with(111)->returns('y');

        $this->assertSame('x', $stub('a', 'b', 'c'));
        $this->assertSame('y', $stub(111));
        $this->assertTrue((bool) $stub->twice()->called());
        $this->assertTrue((bool) $stub->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $stub->calledWith('a', 'b', '~'));
        $this->assertTrue((bool) $stub->calledWith('a', '*'));
        $this->assertTrue((bool) $stub->calledWith('*'));
        $this->assertTrue((bool) $stub->calledWith(111));
        $this->assertTrue((bool) $stub->callAt(0)->calledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $stub->callAt(1)->calledWith(111));
        $this->assertTrue((bool) $stub->returned('x'));
        $this->assertTrue((bool) $stub->returned('y'));

        $this->assertTrue(
            (bool) inOrder(
                $stub->calledWith('a', 'b', 'c'),
                $stub->returned('x'),
                $stub->calledWith(111),
                $stub->returned('y')
            )
        );
    }

    public function testStubMagicSelf()
    {
        $stub = stub(
            function ($phonySelf) {
                return $phonySelf;
            }
        )->forwards();

        $this->assertSame($stub, $stub());
    }

    public function testStubReturnType()
    {
        $stub = stub(function (): int { return 123; })->forwards();

        $this->assertSame(123, $stub());
    }

    public function testStubArrowFunction()
    {
        $stub = stub(eval('return fn($x) => $x;'))->forwards();

        $this->assertSame(111, $stub(111));
    }

    public function testStubGlobal()
    {
        $stubA = stubGlobal('vsprintf', Test::class);

        $this->assertEmpty(Test\vsprintf('%s, %s', ['a', 'b']));

        $stubA->returns('x');

        $this->assertSame('x', Test\vsprintf('%s, %s', ['a', 'b']));

        $stubA->forwards();

        $this->assertSame('a, b', Test\vsprintf('%s, %s', ['a', 'b']));
        $stubA->times(3)->calledWith('%s, %s', ['a', 'b']);

        $stubB = stubGlobal('vsprintf', Test::class);

        $this->assertEmpty(Test\vsprintf('%s, %s', ['a', 'b']));
        $stubB->calledWith('%s, %s', ['a', 'b']);

        $stubB->returns('x');

        $this->assertSame('x', Test\vsprintf('%s, %s', ['a', 'b']));

        restoreGlobalFunctions();

        $this->assertSame('a, b', Test\vsprintf('%s, %s', ['a', 'b']));
    }

    public function testIterableSpying()
    {
        $value = ['a' => 'b', 'c' => 'd'];

        $stub = stub();
        $stub->setUseIterableSpies(true);
        $stub->returns($value);
        $result = $stub();

        $this->assertSame($value, iterator_to_array($result));
        $this->assertSame($value, iterator_to_array($result));

        $this->assertTrue((bool) $stub->iterated()->produced());
        $this->assertTrue((bool) $stub->iterated()->produced('b'));
        $this->assertTrue((bool) $stub->iterated()->produced('d'));
        $this->assertTrue((bool) $stub->iterated()->produced('a', 'b'));
        $this->assertTrue((bool) $stub->iterated()->produced('c', 'd'));

        $this->assertSame('b', $result['a']);
        $this->assertCount(2, $result);
    }

    public function testIterableSpyingWithArrayLikeObject()
    {
        $value = ['a' => 'b', 'c' => 'd'];

        $stub = stub();
        $stub->setUseIterableSpies(true);
        $stub->returns(new ArrayObject($value));
        $result = $stub();

        $this->assertSame($value, iterator_to_array($result));
        $this->assertSame($value, iterator_to_array($result));

        $this->assertTrue((bool) $stub->iterated()->produced());
        $this->assertTrue((bool) $stub->iterated()->produced('b'));
        $this->assertTrue((bool) $stub->iterated()->produced('d'));
        $this->assertTrue((bool) $stub->iterated()->produced('a', 'b'));
        $this->assertTrue((bool) $stub->iterated()->produced('c', 'd'));

        $this->assertSame('b', $result['a']);
        $this->assertCount(2, $result);
    }

    public function testDefaultStubAnswerCanBeOverridden()
    {
        $handle = partialMock(TestClassA::class);
        $handle->testClassAMethodA->with('a', 'b')->returns(123);
        $mock = $handle->get();

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));
        $this->assertSame('ef', $mock->testClassAMethodB('e', 'f'));
    }

    public function testFullMockDefaultStubAnswerCanBeOverridden()
    {
        $handle = mock(TestClassA::class);
        $mock = $handle->get();
        $handle->testClassAMethodA->with('a', 'b')->returns(123);

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertNull($mock->testClassAMethodA('c', 'd'));
        $this->assertNull($mock->testClassAMethodB('e', 'f'));
    }

    public function testMagicMockDefaultStubAnswerCanBeOverridden()
    {
        $handle = mock(TestClassB::class);
        $mock = $handle->get();
        $handle->nonexistentA->with('a', 'b')->returns(123);

        $this->assertSame(123, $mock->nonexistentA('a', 'b'));
        $this->assertNull($mock->nonexistentA('c', 'd'));
        $this->assertNull($mock->nonexistentB('e', 'f'));
    }

    public function testDoesntCallParentOnInterfaceOnlyMock()
    {
        $handle = partialMock(TestInterfaceA::class);
        $mock = $handle->get();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));
    }

    public function testDefaultArgumentsNotRecorded()
    {
        $handle = partialMock(TestClassC::class);
        $handle->get()->methodB('a');

        $this->assertTrue((bool) $handle->methodB->calledWith('a'));
    }

    public function testHandleStubOverriding()
    {
        $handle = partialMock(TestClassA::class);
        $handle->testClassAMethodA->returns('x');
        $handle->testClassAMethodA->returns('y', 'z');

        $this->assertSame('y', $handle->get()->testClassAMethodA());
        $this->assertSame('z', $handle->get()->testClassAMethodA());
        $this->assertSame('z', $handle->get()->testClassAMethodA());
    }

    public function testCanCallMockedInterfaceMethod()
    {
        $handle = partialMock([stdClass::class, TestInterfaceA::class]);

        $this->assertNull($handle->get()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedInterfaceMethodWithoutParentClass()
    {
        $handle = partialMock(TestInterfaceA::class);

        $this->assertNull($handle->get()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethod()
    {
        $handle = partialMock([stdClass::class, TestTraitA::class]);

        $this->assertSame('ab', $handle->get()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithoutParentClass()
    {
        $handle = partialMock([TestTraitA::class]);

        $this->assertSame('ab', $handle->get()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethod()
    {
        $handle = partialMock([stdClass::class, TestTraitC::class]);

        $this->assertNull($handle->get()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethodWithoutParentClass()
    {
        $handle = partialMock([TestTraitC::class]);

        $this->assertNull($handle->get()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithInterface()
    {
        $handle = partialMock([TestTraitH::class, TestInterfaceE::class]);

        $this->assertSame('a', $handle->get()->methodA());
    }

    public function testCanMockClassWithPrivateConstructor()
    {
        $handle = partialMock(TestClassD::class);

        $this->assertInstanceOf(TestClassD::class, $handle->get());
    }

    public function testCanMockTraitWithPrivateConstructor()
    {
        $handle = partialMock(TestTraitF::class, ['a', 'b']);

        $this->assertSame(['a', 'b'], $handle->get()->constructorArguments);
    }

    public function testCanMockClassAndCallPrivateConstructor()
    {
        $handle = partialMock(TestClassD::class, ['a', 'b']);

        $this->assertSame(['a', 'b'], $handle->get()->constructorArguments);
    }

    public function testMatcherAdaptationForBooleanValues()
    {
        $handle = mock(TestClassA::class);
        $handle->testClassAMethodA->with(true)->returns('a');

        $this->assertNull($handle->get()->testClassAMethodA());
    }

    public function testAssertionExceptionTrimming()
    {
        $spy = spy();
        $exception = null;

        try {
            $line = __LINE__ + 1;
            $spy->called();
        } catch (Exception $exception) {
        }

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame(__FILE__, $exception->getFile());
        $this->assertSame($line, $exception->getLine());

        $trace = $exception->getTrace();

        $this->assertIsArray($trace);
        $this->assertArrayHasKey(0, $trace);

        $entry = $trace[0];

        $this->assertIsArray($entry);
        $this->assertSame(SpyVerifier::class, $entry['class']);
        $this->assertSame('called', $entry['function']);
        $this->assertSame(__FILE__, $entry['file']);
        $this->assertSame($line, $entry['line']);
        $this->assertSame('->', $entry['type']);

        if (array_key_exists('args', $entry)) {
            $this->assertSame([], $entry['args']);
        }
    }

    public function testAssertionExceptionTrimmingWithEmptyTrace()
    {
        $exception = new Exception();
        $reflector = new ReflectionClass(Exception::class);
        $traceProperty = $reflector->getProperty('trace');
        $traceProperty->setAccessible(true);
        $traceProperty->setValue($exception, []);
        AssertionException::trim($exception);

        $this->assertEmpty($exception->getFile());
        $this->assertEmpty($exception->getLine());
        $this->assertSame([], $exception->getTrace());
    }

    public function testHandleCaseInsensitivity()
    {
        $handle = partialMock(TestClassA::class);

        $this->assertSame($handle->testClassAMethodA, $handle->testclassamethoda);
    }

    public function testIterableInterfaceMocking()
    {
        partialMock(TestInterfaceC::class);

        $this->assertTrue(true);
    }

    public function testIterableInterfaceMockingWithPDOStatement()
    {
        $this->assertInstanceOf(PDOStatement::class, mock(PDOStatement::class)->get());
    }

    public function testTraitConstructorCalling()
    {
        $handle = partialMock(TestTraitD::class, ['a', 'b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $handle->get()->constructorArguments);
    }

    public function testTraitConstructorConflictResolution()
    {
        $handle = partialMock(
            [TestTraitD::class, TestTraitE::class],
            ['a', 'b', 'c']
        );

        $this->assertSame(['a', 'b', 'c'], $handle->get()->constructorArguments);
    }

    public function testCallAtWithAssertionResult()
    {
        $spy = spy();
        $spy('a', 1);
        $spy('b', 1);
        $spy('a', 2);

        $this->assertSame(['a', 2], $spy->calledWith('a', '*')->callAt(1)->arguments()->all());
    }

    public function testPhonySelfMagicParameter()
    {
        $handle = mock(TestClassA::class);
        $callArguments = null;
        $handle->testClassAMethodA
            ->calls(
                function ($phonySelf) use (&$callArguments) {
                    $callArguments = func_get_args();
                }
            )
            ->does(
                function ($phonySelf) {
                    return $phonySelf;
                }
            );

        $this->assertSame($handle->get(), $handle->get()->testClassAMethodA());
        $this->assertSame([$handle->get()], $callArguments);
    }

    public function testOrderVerification()
    {
        $spy = spy();
        $spy('a');
        $spy('b');
        $spy('c');
        $spy('d');

        $this->assertTrue(
            (bool) inOrder(
                $spy->calledWith('a'),
                anyOrder(
                    $spy->calledWith('c'),
                    $spy->calledWith('b')
                ),
                $spy->calledWith('d')
            )
        );
    }

    public function testCanForwardAfterFullMock()
    {
        $handle = mock(TestClassA::class);
        $mock = $handle->get();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));

        $handle->testClassAMethodA->returns('x');

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));

        $handle->testClassAMethodA->forwards();

        $this->assertSame('ab', $mock->testClassAMethodA('a', 'b'));
    }

    public function testCanForwardToMagicCallAfterFullMock()
    {
        $handle = mock(TestClassB::class);
        $mock = $handle->get();

        $this->assertNull($mock->nonexistent());

        $handle->nonexistent->returns('a');

        $this->assertSame('a', $mock->nonexistent());

        $handle->__call->forwards();
        $handle->nonexistent->forwards();

        $this->assertSame('magic nonexistent a', $mock->nonexistent('a'));
    }

    public function testCanForwardToMagicCallAfterPartialMock()
    {
        $handle = partialMock(TestClassB::class);
        $mock = $handle->get();

        $this->assertSame('magic nonexistent a', $mock->nonexistent('a'));

        $handle->nonexistent->returns('a');

        $this->assertSame('a', $mock->nonexistent());

        $handle->__call->forwards();
        $handle->nonexistent->forwards();

        $this->assertSame('magic nonexistent a', $mock->nonexistent('a'));
    }

    public function testCanMockExceptions()
    {
        $handle = mock(Exception::class);

        $this->assertInstanceOf(Exception::class, $handle->get());
    }

    public function testMockMethodAssertionRenderingWithRealMethod()
    {
        $mock = mockBuilder(TestClassA::class)->named('PhonyMockAssertionRenderingWithRealMethod')->get();
        $handle = on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->testClassAMethodA->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertStringContainsString(
            'Expected TestClassA[label]->testClassAMethodA call with arguments',
            $error->getMessage()
        );
    }

    public function testMockMethodAssertionRenderingWithMagicMethod()
    {
        $mock = mockBuilder(TestClassB::class)->named('PhonyMockAssertionRenderingWithMagicMethod')->get();
        $handle = on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->magicMethod->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertStringContainsString(
            'Expected TestClassB[label]->magicMethod call with arguments',
            $error->getMessage()
        );
    }

    public function testMockMethodAssertionRenderingWithUncallableMethod()
    {
        $mock =
            mockBuilder(IteratorAggregate::class)->named('PhonyMockAssertionRenderingWithUncallableMethod')->get();
        $handle = on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->getIterator->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertStringContainsString(
            'Expected IteratorAggregate[label]->getIterator call with arguments',
            $error->getMessage()
        );
    }

    public function testMockMethodAssertionRenderingWithCustomMethod()
    {
        $mock = mockBuilder()->named('PhonyMockAssertionRenderingWithCustomMethod')->addMethod('customMethod')->get();
        $handle = on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->customMethod->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertStringContainsString(
            'Expected PhonyMockAssertionRenderingWithCustomMethod[label]->customMethod call with arguments',
            $error->getMessage()
        );
    }

    public function testCanCallCustomMethodWithInvocableObjectImplementation()
    {
        $mock = partialMock(['methodA' => new TestInvocable()])->get();

        $this->assertSame(['invokeWith', ['a', 'b']], $mock->methodA('a', 'b'));
    }

    public function testMockWithUncallableMagicMethod()
    {
        $mock = mock(TestInterfaceD::class)->get();

        $this->assertNull($mock->nonexistent());
    }

    public function testNoInteraction()
    {
        $handle = mock(TestInterfaceD::class);

        $this->assertTrue((bool) $handle->noInteraction());
    }

    public function testCallsArgumentWithFullMockImplicitReturns()
    {
        $handle = Phony::mock(TestClassA::class);
        $handle->testClassAMethodA->callsArgument(0);
        $spy = Phony::spy();

        $this->assertNull($handle->get()->testClassAMethodA($spy));
        $this->assertTrue((bool) $spy->called());
    }

    public function testIncompleteCalls()
    {
        $test = $this;
        $context = (object) ['spy' => null];
        $context->spy = $spy = spy(
            function () use ($test, $context) {
                $test->assertFalse($context->spy->callAt(0)->hasResponded());
                $test->assertFalse($context->spy->callAt(0)->hasCompleted());
            }
        );

        $spy();
    }

    public function testCallRespondedAndCompleted()
    {
        $stub = stub();
        $stub->returns([], []);
        $stub();
        $stub->setUseIterableSpies(true);
        $stub();
        $callA = $stub->callAt(0);
        $callB = $stub->callAt(1);

        $this->assertTrue($callA->hasResponded());
        $this->assertTrue($callA->hasCompleted());
        $this->assertTrue($callB->hasResponded());
        $this->assertFalse($callB->hasCompleted());
    }

    public function testCannotMockAnonymousClasses()
    {
        $instance = new class() {};

        $this->expectException(AnonymousClassException::class);
        mock(get_class($instance));
    }

    public function testPartialMockOfMagicCallTrait()
    {
        $handle = partialMock(TestTraitJ::class);
        $mock = $handle->get();

        $this->assertSame('magic a bc', $mock->a('b', 'c'));
        $this->assertTrue((bool) $handle->a->calledWith('b', 'c'));
    }

    public function testPartialMockOfStaticMagicCallTrait()
    {
        $mock = partialMock(TestTraitJ::class)->get();
        $class = get_class($mock);

        $this->assertSame('magic a bc', $class::a('b', 'c'));
        $this->assertTrue((bool) onStatic($mock)->a->calledWith('b', 'c'));
    }

    public function testInvalidStubUsageWithInvoke()
    {
        $stub = stub()->with();

        $this->expectException(UnusedStubCriteriaException::class);
        $stub();
    }

    public function testMockHandleSubstitution()
    {
        $handleA = mock();
        $handleA->get();
        $handleB = mock(['methodA' => function () {}]);
        $mockB = $handleB->get();
        $handleB->methodA->returns($handleA);
        $handleB->methodA->with($handleA)->returns('a');

        $this->assertSame($handleA->get(), $mockB->methodA());
        $this->assertSame('a', $mockB->methodA($handleA->get()));
        $this->assertTrue((bool) $handleB->methodA->calledWith($handleA));
        $this->assertTrue((bool) $handleB->methodA->returned($handleA));
    }

    public function testIterableSpySubstitution()
    {
        $stub = stub()->setUseIterableSpies(true)->returnsArgument();
        $iterable = ['a', 'b'];
        $iterableSpy = $stub($iterable);
        $spy = spy();
        $spy($iterable);
        $spy($iterableSpy);

        $this->assertTrue((bool) $stub->returned($iterable));
        $this->assertTrue((bool) $stub->returned($iterableSpy));
        $this->assertTrue((bool) $stub->returned(equalTo($iterable)));
        $this->assertTrue((bool) $stub->never()->returned(equalTo($iterableSpy)));
        $this->assertTrue((bool) $spy->callAt(0)->calledWith($iterable));
        $this->assertTrue((bool) $spy->callAt(0)->calledWith($iterableSpy));
        $this->assertTrue((bool) $spy->callAt(0)->never()->calledWith(equalTo($iterableSpy)));
        $this->assertTrue((bool) $spy->callAt(1)->calledWith($iterable));
        $this->assertTrue((bool) $spy->callAt(1)->calledWith($iterableSpy));
        $this->assertTrue((bool) $spy->callAt(1)->never()->calledWith(equalTo($iterable)));
    }

    public function testReturnByReferenceMocking()
    {
        $a = 'a';
        $b = 'b';
        $handle = partialMock(TestClassG::class);
        $mock = $handle->get();
        $class = get_class($mock);
        $static = $class::testClassGStaticMethodA(true, $a, $b);
        $staticMagic = $class::nonexistent(true, $a, $b);
        $nonStatic = $mock->testClassGMethodA(true, $a, $b);
        $nonStaticMagic = $mock->nonexistent(true, $a, $b);

        $this->assertSame('a', $static);
        $this->assertSame('a', $staticMagic);
        $this->assertSame('a', $nonStatic);
        $this->assertSame('a', $nonStaticMagic);

        $a = 'x';

        $this->assertSame('a', $static);
        $this->assertSame('a', $staticMagic);
        $this->assertSame('a', $nonStatic);
        $this->assertSame('a', $nonStaticMagic);
    }

    public function testAdHocMocksWithSameSignatures()
    {
        $foo = partialMock(['test' => function () { return 'foo'; }])->get();
        $bar = partialMock(['test' => function () { return 'bar'; }])->get();

        $this->assertSame('foo', $foo->test());
        $this->assertSame('bar', $bar->test());
    }

    public function testAdHocMocksWithMagicSelf()
    {
        $mock = partialMock(['test' => function ($phonySelf) { return $phonySelf; }])->get();

        $this->assertSame($mock, $mock->test());
    }

    public function testAdHocMocksWithMagicSelfOutput()
    {
        $builder = mockBuilder(['test' => function ($phonySelf) { return $phonySelf; }])
            ->named('PhonyTestAdHocMocksWithMagicSelfOutput');
        $mock = $builder->get();
        $handle = on($mock)->setLabel('label');

        $this->expectException(AssertionException::class);
        $this->expectExceptionMessage('PhonyTestAdHocMocksWithMagicSelfOutput[label]->test');
        $handle->test->calledWith('a');
    }

    public function testAdHocMocksWithCustomMethodStringCallable()
    {
        $mock = partialMock(['function test' => 'implode'])->get();

        $this->assertSame('a, b', $mock->test(', ', ['a', 'b']));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testAdHocMocksWithReadonlyProperties()
    {
        $mock = partialMock([
            'readonly string propertyA' => null,
            'readonly ?int propertyB' => null,
            'init' => function ($phonySelf) {
                $reflectorA = new ReflectionProperty($phonySelf, 'propertyA');
                $reflectorA->setValue($phonySelf, 'a');
                $reflectorB = new ReflectionProperty($phonySelf, 'propertyB');
                $reflectorB->setValue($phonySelf, null);
            },
        ])->get();
        $mock->init();

        $this->assertSame('a', $mock->propertyA);
        $this->assertNull($mock->propertyB);

        $this->expectExceptionMessage('Cannot modify readonly property');
        $mock->propertyB = 111;
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testAdHocMocksDocExample()
    {
        $handle = partialMock(
            [
                'const A' => 'A is for apple.',
                'static b' => function () {
                    return 'B is for banana.';
                },
                'static c' => 'C is for cat.',
                'string d' => 'D is for dog.',

                'function e' => 'implode',

                'readonly string f' => null,
                'initalizeF' => function ($phonySelf) {
                    // this scope is not the same as the declaration scope
                    // therefore reflection must be used to initialize the property
                    $f = new ReflectionProperty($phonySelf, 'f');
                    $f->setValue($phonySelf, 'F is for final.');
                },
            ]
        );

        $mock = $handle->get();
        $class = get_class($mock);

        $this->assertSame('A is for apple.', $class::A);
        $this->assertSame('B is for banana.', $class::b());
        $this->assertSame('C is for cat.', $class::$c);
        $this->assertSame('D is for dog.', $mock->d);

        $this->assertSame('a, b', $mock->e(', ', ['a', 'b']));

        $mock->initalizeF();
        $this->assertSame('F is for final.', $mock->f);
        $this->expectExceptionMessage('Cannot modify readonly property');
        $mock->f = 'F is for foolishness.';
    }

    public function testBasicGeneratorStubbing()
    {
        $stub = stub()
            ->generates(['a' => 'b', 'c'])
                ->yields('d', 'e')
                ->yields('f')
                ->yields()
                ->returns();

        $generator = $stub();
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf(Generator::class, $generator);
        $this->assertSame(['a' => 'b', 0 => 'c', 'd' => 'e', 1 => 'f', 2 => null], $actual);
    }

    public function testGeneratorStubbingWithReturnValue()
    {
        $stub = stub()->generates()->returns('d');

        $generator = $stub();
        iterator_to_array($generator);

        $this->assertInstanceOf(Generator::class, $generator);
        $this->assertSame('d', $generator->getReturn());
    }

    public function testGeneratorStubbingWithMultipleAnswers()
    {
        $stub = stub()
            ->generates()->yields('a')->returns()
            ->returns('b')
            ->generates()->yields('c')->returns();

        $this->assertSame(['a'], iterator_to_array($stub()));
        $this->assertSame('b', $stub());
        $this->assertSame(['c'], iterator_to_array($stub()));
    }

    public function testGeneratorStubbingWithEmptyGenerator()
    {
        $stub = stub();
        $stub->generates();

        $generator = $stub();
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf(Generator::class, $generator);
        $this->assertSame([], $actual);
    }

    public function testAssertionExceptionConstruction()
    {
        $actual = new AssertionException('You done goofed.');

        $this->assertNotNull($actual);
    }

    public function testFinalConstructorBypass()
    {
        $handle = mock(TestClassI::class);
        $mock = $handle->get();

        $this->assertNull($mock->constructorArguments);
    }

    public function testIterableSpyDoubleWrappingWithArray()
    {
        $stub = stub()->setUseIterableSpies(true)->returns(['a', 'b']);
        $iterableSpyA = $stub();
        $stub->returns($iterableSpyA);
        $iterableSpyB = $stub();
        foreach ($iterableSpyA as $iterableSpyAFirst) {
            break;
        }
        $iterableSpyBContents = iterator_to_array($iterableSpyB);
        $singleWrapped = $stub->callAt(0)->iterated();
        $doubleWrapped = $stub->callAt(1)->iterated();

        $this->assertSame('a', $iterableSpyAFirst);
        $this->assertSame(['a', 'b'], $iterableSpyBContents);
        $this->assertTrue((bool) $singleWrapped->twice()->produced('a'));
        $this->assertTrue((bool) $singleWrapped->once()->produced('b'));
        $this->assertTrue((bool) $doubleWrapped->once()->produced('a'));
        $this->assertTrue((bool) $doubleWrapped->once()->produced('b'));
    }

    public function testIterableSpyDoubleWrappingWithTraversable()
    {
        $stub = stub()->setUseIterableSpies(true)->returns(new ArrayIterator(['a', 'b']));
        $iterableSpyA = $stub();
        $stub->returns($iterableSpyA);
        $iterableSpyB = $stub();
        $iterableSpyAFirst = null;
        foreach ($iterableSpyA as $iterableSpyAFirst) {
            break;
        }
        $iterableSpyBContents = iterator_to_array($iterableSpyB);
        $singleWrapped = $stub->callAt(0)->iterated();
        $doubleWrapped = $stub->callAt(1)->iterated();

        $this->assertSame('a', $iterableSpyAFirst);
        $this->assertSame(['a', 'b'], $iterableSpyBContents);
        $this->assertTrue((bool) $singleWrapped->twice()->produced('a'));
        $this->assertTrue((bool) $singleWrapped->once()->produced('b'));
        $this->assertTrue((bool) $doubleWrapped->once()->produced('a'));
        $this->assertTrue((bool) $doubleWrapped->once()->produced('b'));
    }

    public function testIterableSpyDoubleWrappingWithGenerator()
    {
        $stub = stub()->generates()->yieldsFrom(['a', 'b', 'c'])->returns();
        $generatorSpyA = $stub();
        $stub->returns($generatorSpyA);
        $generatorSpyB = $stub();

        $first = true;
        $generatorSpyAContents = [];
        foreach ($generatorSpyA as $value) {
            $generatorSpyAContents[] = $value;

            if ($first) {
                $first = false;

                continue;
            }

            break;
        }
        $generatorSpyBContents = iterator_to_array($generatorSpyB);
        $singleWrapped = $stub->callAt(0)->generated();
        $doubleWrapped = $stub->callAt(1)->generated();

        $this->assertSame(['a', 'b'], $generatorSpyAContents);
        $this->assertSame([1 => 'b', 2 => 'c'], $generatorSpyBContents);
        $this->assertTrue((bool) $singleWrapped->once()->produced(0, 'a'));
        $this->assertTrue((bool) $singleWrapped->once()->produced(1, 'b'));
        $this->assertTrue((bool) $singleWrapped->once()->produced(2, 'c'));
        $this->assertTrue((bool) $doubleWrapped->never()->produced('a'));
        $this->assertTrue((bool) $doubleWrapped->once()->produced(1, 'b'));
        $this->assertTrue((bool) $doubleWrapped->once()->produced(2, 'c'));
    }

    public function exporterExamplesTest()
    {
        $sequence = [1, 2];
        $repeatedSequences = [&$sequence, &$sequence];
        $inner = (object) ['a' => 1];
        $repeatedObjects = (object) ['b' => $inner, 'c' => $inner];
        $identifierCollision = [(object) [], [(object) []]];
        $inner = new ClassWithProperty();
        $inner->c = 'd';
        $classNameExclusion = (object) ['a' => $inner, 'b' => $inner];

        return [
            // The exporter format
            'null'           => [null,                          'null'],
            'true'           => [true,                          'true'],
            'false'          => [false,                         'false'],
            'integer'        => [111,                           '111'],
            'float'          => [1.11,                          '1.110000e+0'],
            'float string'   => ['1.11',                        '"1.11"'],
            'string'         => ["a\nb",                        '"a\nb"'],
            'resource'       => [STDIN,                         'resource#%d'],
            'sequence'       => [$sequence,                     '#%d[1, 2]'],
            'map'            => [['a' => 1, 'b' => 2],          '#%d["a": 1, "b": 2]'],
            'generic object' => [(object) ['a' => 1, 'b' => 2], '#%d{a: 1, b: 2}'],
            'object'         => [new ClassA(),                  'ClassA#%d{}'],

            // Export identifiers and references
            'repeated sequence'       => [$repeatedSequences, '#%d[#%d[1, 2], &%d]'],
            'repeated generic object' => [$repeatedObjects,   '#%d{b: #%d{a: 1}, c: &%d}'],

            // Export reference types
            'identifier collision' => [$identifierCollision, '#%d[#%d{}, #%d[#%d{}]]'],

            // Export reference exclusions
            'class name exclusion' => [$classNameExclusion, '#%d{a: ClassWithProperty#%d{c: "d"}, b: &%d}'],

            // Exporting closures
            'closure' => [function () {}, 'Closure#%d{}[FunctionalTest.php:' . __LINE__ . ']'],

            // Exporting exceptions
            'exception'           => [new Exception('a', 1, new Exception()), 'Exception#%d{message: "a", code: 1, previous: Exception#%d{}}'],
            'exception defaulted' => [new RuntimeException(),                 'RuntimeException#%d{}'],
        ];
    }

    /**
     * @dataProvider exporterExamplesTest
     */
    public function testExporterExamples($value, $expected)
    {
        $this->exporter->reset();

        $this->assertStringMatchesFormat($expected, $this->exporter->export($value, -1));
    }

    public function testExporterExamplesRepeatedWrappers()
    {
        $inner = mock(ClassA::class)->setLabel('mock-label');
        $value = [$inner, $inner];
        $this->exporter->reset();

        $this->assertStringMatchesFormat(
            '#%d[handle#%d(PhonyMock_ClassA_%d#%d{}[mock-label]), &%d]',
            $this->exporter->export($value, -1)
        );
    }

    public function testExporterExamplesReferenceTypes()
    {
        $array = [];
        $object = (object) [];
        $weakRef = WeakReference::create($object);
        $wrapper = spy('implode')->setLabel('spy-label');
        $valueA = [&$array, &$array];
        $valueB = [$object, $object];
        $valueC = [$weakRef, $weakRef];
        $valueD = [$wrapper, $wrapper];
        $this->exporter->reset();

        $this->assertStringMatchesFormat('#%d[#%d[], &%d]', $this->exporter->export($valueA, -1));
        $this->assertStringMatchesFormat('#%d[#%d{}, &%d]', $this->exporter->export($valueB, -1));
        $this->assertStringMatchesFormat('#%d[weak#%d(#%d{}), &%d]', $this->exporter->export($valueC, -1));
        $this->assertStringMatchesFormat(
            '#%d[spy#%d(implode)[spy-label], &%d]',
            $this->exporter->export($valueD, -1)
        );
    }

    public function testExporterExamplesExcludeWrapperValue()
    {
        $inner = mock();
        $value = [$inner, $inner];
        $this->exporter->reset();

        $this->assertStringMatchesFormat(
            '#%d[handle#%d(PhonyMock_%d#%d{}[%d]), &%d]',
            $this->exporter->export($value, -1)
        );
    }

    public function testExporterExamplesIdentifierPersistenceObjects()
    {
        $a = (object) [];
        $b = (object) [];
        $c = mock();
        $valueA = [$a, $b, $c, $a];
        $valueB = [$b, $a, $b, $c];
        $this->exporter->reset();

        $this->assertStringMatchesFormat(
            '#%d[#%d{}, #%d{}, handle#%d(PhonyMock_%d#%d{}[%d]), &%d]',
            $this->exporter->export($valueA, -1)
        );
        $this->assertStringMatchesFormat(
            '#%d[#%d{}, #%d{}, &%d, handle#%d(PhonyMock_%d#%d{}[%d])]',
            $this->exporter->export($valueB, -1)
        );
    }

    public function testExporterExamplesIdentifierPersistenceArrays()
    {
        $a = [];
        $b = [];
        $valueA = [&$a, &$b, &$a];
        $valueB = [&$b, &$a, &$b];
        $this->exporter->reset();

        $this->assertStringMatchesFormat('#%d[#%d[], #%d[], &%d]', $this->exporter->export($valueA, -1));
        $this->assertStringMatchesFormat('#%d[#%d[], #%d[], &%d]', $this->exporter->export($valueB, -1));
    }

    public function testExporterExamplesRecursiveValues()
    {
        $recursiveArray = [];
        $recursiveArray[] = &$recursiveArray;
        $recursiveObject = (object) [];
        $recursiveObject->a = $recursiveObject;
        $this->exporter->reset();

        $this->assertStringMatchesFormat('#%d[&%d]', $this->exporter->export($recursiveArray, -1));
        $this->assertStringMatchesFormat('#%d{a: &%d}', $this->exporter->export($recursiveObject, -1));
    }

    public function testExporterExamplesMocks()
    {
        $handle = mock(ClassA::class)->setLabel('mock-label');
        $mock = $handle->get();
        $this->exporter->reset();

        $this->assertStringMatchesFormat('PhonyMock_ClassA_%d#%d{}[mock-label]', $this->exporter->export($mock, -1));
        $this->assertStringMatchesFormat(
            'handle#%d(PhonyMock_ClassA_%d#%d{}[mock-label])',
            $this->exporter->export($handle, -1)
        );
    }

    public function testExporterExamplesStaticHandle()
    {
        $handle = mock(ClassA::class)->setLabel('mock-label');
        $staticHandle = onStatic($handle);
        $this->exporter->reset();

        $this->assertStringMatchesFormat(
            'static-handle#%d(PhonyMock_ClassA_%d)',
            $this->exporter->export($staticHandle, -1)
        );
    }

    public function testExporterExamplesStubs()
    {
        $stub = stub('implode')->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertStringMatchesFormat('stub#%d(implode)[stub-label]', $this->exporter->export($stub, -1));
    }

    public function testExporterExamplesAnonymousStubs()
    {
        $stub = stub()->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertStringMatchesFormat('stub#%d[stub-label]', $this->exporter->export($stub, -1));
    }

    public function testExporterExamplesMockStubs()
    {
        $handle = mock(ClassA::class)->setLabel('mock-label');
        $staticHandle = onStatic($handle);
        $stubA = $handle->methodA->setLabel('stub-label');
        $stubB = $staticHandle->staticMethodA->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertStringMatchesFormat(
            'stub#%d(ClassA[mock-label]->methodA)[stub-label]',
            $this->exporter->export($stubA, -1)
        );
        $this->assertStringMatchesFormat(
            'stub#%d(ClassA::staticMethodA)[stub-label]',
            $this->exporter->export($stubB, -1)
        );
    }

    public function testExporterExamplesSpies()
    {
        $spy = spy('implode')->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertStringMatchesFormat('spy#%d(implode)[spy-label]', $this->exporter->export($spy, -1));
    }

    public function testExporterExamplesAnonymousSpies()
    {
        $spy = spy()->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertStringMatchesFormat('spy#%d[spy-label]', $this->exporter->export($spy, -1));
    }

    public function testExporterExamplesMethodSpies()
    {
        $object = new ClassA();
        $spyA = spy([$object, 'methodA'])->setLabel('spy-label');
        $spyB = spy([ClassA::class, 'staticMethodA'])->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertStringMatchesFormat('spy#%d(ClassA->methodA)[spy-label]', $this->exporter->export($spyA, -1));
        $this->assertStringMatchesFormat(
            'spy#%d(ClassA::staticMethodA)[spy-label]',
            $this->exporter->export($spyB, -1)
        );
    }

    public function testExporterExamplesExportDepth()
    {
        $valueA = [[], ['a', 'b', 'c']];
        $valueB = [(object) [], (object) ['a', 'b', 'c']];
        $this->exporter->reset();

        $this->assertStringMatchesFormat('#%d[#%d[], #%d[~3]]', $this->exporter->export($valueA));
        $this->assertStringMatchesFormat('#%d[#%d{}, #%d{~3}]', $this->exporter->export($valueB));
    }

    public function testReturnsVariadic()
    {
        $stub = stub()->returns('a')->returns()->returns('b');

        $this->assertSame('a', $stub());
        $this->assertNull($stub());
        $this->assertSame('b', $stub());
    }

    public function testMockDumping()
    {
        $handle = mock(TestClassA::class);
        $static = onStatic($handle);
        $mock = $handle->get();

        ob_start();
        var_dump($handle, $static, $mock);
        $output = ob_get_clean();

        $this->assertLessThan(800, strlen($output), 'Mock dumping produced excessive output: ' . $output);
    }

    public function testStubDumping()
    {
        $stub = stub('implode');

        ob_start();
        var_dump($stub);
        $output = ob_get_clean();

        $this->assertLessThan(200, strlen($output), 'Stub dumping produced excessive output: ' . $output);
    }

    public function testSpyDumping()
    {
        $spy = spy('implode');

        ob_start();
        var_dump($spy);
        $output = ob_get_clean();

        $this->assertLessThan(200, strlen($output), 'Spy dumping produced excessive output: ' . $output);
    }

    public function testFinalReturnValueWithStub()
    {
        $expected = new TestFinalClassA();
        $stub = stub(Test::class . '\testFunctionWithFinalReturnType');
        $stub->returns($expected);

        $this->assertSame($expected, $stub());
    }

    public function testFinalDefaultReturnValueWithStub()
    {
        $stub = stub(Test::class . '\testFunctionWithFinalReturnType');

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessage(
            'Unable to create a default return value for ' .
                "'Eloquent\\\\Phony\\\\Test\\\\testFunctionWithFinalReturnType', which has a final return type of " .
                "'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
        );
        $stub();
    }

    public function testFinalReturnValueWithMock()
    {
        $expected = new TestFinalClassA();
        $handle = mock(TestClassWithFinalReturnType::class);
        $handle->finalReturnType->returns($expected);
        $handle->undefined->returns($expected);
        $mock = $handle->get();

        $this->assertSame($expected, $mock->finalReturnType());
        $this->assertSame($expected, $mock->undefined());
    }

    public function testFinalReturnValueWithMockForwarding()
    {
        $handle = mock(TestClassWithFinalReturnType::class);
        $handle->finalReturnType->forwards();
        $handle->undefined->forwards();
        $mock = $handle->get();

        $this->assertInstanceOf(TestFinalClassA::class, $mock->finalReturnType());
        $this->assertInstanceOf(TestFinalClassA::class, $mock->undefined());
    }

    public function testFinalReturnValueWithPartialMock()
    {
        $handle = partialMock(TestClassWithFinalReturnType::class);
        $mock = $handle->get();

        $this->assertInstanceOf(TestFinalClassA::class, $mock->finalReturnType());
        $this->assertInstanceOf(TestFinalClassA::class, $mock->undefined());
    }

    public function testFinalDefaultReturnValueWithMock()
    {
        $mock = mock(TestClassWithFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'TestClassWithFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->finalReturnType', which has a final return type of 'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
            ) .
            '$/'
        );
        $mock->finalReturnType();
    }

    public function testFinalDefaultReturnValueWithMockMagic()
    {
        $mock = mock(TestClassWithFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'TestClassWithFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->undefined', which has a final return type of 'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
            ) .
            '$/'
        );
        $mock->undefined();
    }

    public function testFinalReturnValueWithAbstractMock()
    {
        $expected = new TestFinalClassA();
        $handle = mock(AbstractTestClassWithFinalReturnType::class);
        $handle->finalReturnType->returns($expected);
        $handle->undefined->returns($expected);
        $mock = $handle->get();

        $this->assertSame($expected, $mock->finalReturnType());
        $this->assertSame($expected, $mock->undefined());
    }

    public function testFinalDefaultReturnValueWithAbstractMock()
    {
        $mock = mock(AbstractTestClassWithFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'AbstractTestClassWithFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->finalReturnType', which has a final return type of 'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
            ) .
            '$/'
        );
        $mock->finalReturnType();
    }

    public function testFinalDefaultReturnValueWithAbstractMockMagic()
    {
        $mock = mock(AbstractTestClassWithFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'AbstractTestClassWithFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->undefined', which has a final return type of 'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
            ) .
            '$/'
        );
        $mock->undefined();
    }

    public function testFinalReturnValueWithInterfaceMock()
    {
        $expected = new TestFinalClassA();
        $handle = mock(TestInterfaceWithFinalReturnType::class);
        $handle->finalReturnType->returns($expected);
        $handle->undefined->returns($expected);
        $mock = $handle->get();

        $this->assertSame($expected, $mock->finalReturnType());
        $this->assertSame($expected, $mock->undefined());
    }

    public function testFinalDefaultReturnValueWithInterfaceMock()
    {
        $mock = mock(TestInterfaceWithFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'TestInterfaceWithFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->finalReturnType', which has a final return type of 'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
            ) .
            '$/'
        );
        $mock->finalReturnType();
    }

    public function testFinalDefaultReturnValueWithInterfaceMockMagic()
    {
        $mock = mock(TestInterfaceWithFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'TestInterfaceWithFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->undefined', which has a final return type of 'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
            ) .
            '$/'
        );
        $mock->undefined();
    }

    public function testFinalUnionDefaultReturnValue()
    {
        $mock = mock(TestInterfaceWithUnionFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'TestInterfaceWithUnionFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->finalReturnType', which has a final return type of 'Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA|Eloquent\\\\Phony\\\\Test\\\\TestFinalClassB'."
            ) .
            '$/'
        );
        $mock->finalReturnType();
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testFinalIntersectionDefaultReturnValue()
    {
        $mock = mock(TestInterfaceWithIntersectionFinalReturnType::class)->get();

        $this->expectException(FinalReturnTypeException::class);
        $this->expectExceptionMessageMatches(
            '/^' .
            preg_quote("Unable to create a default return value for 'TestInterfaceWithIntersectionFinalReturnType[", '/') .
            '\d+' .
            preg_quote(
                "]->finalReturnType', which has a final return type of 'Countable&Eloquent\\\\Phony\\\\Test\\\\TestFinalClassA'."
            ) .
            '$/'
        );
        $mock->finalReturnType();
    }

    public function testMockWithCustomSerializationMethods()
    {
        $handle = partialMock(TestClassWithSerializeMagicMethods::class, [['a', 'b']]);
        $mock = $handle->get();

        $this->assertSame(['a', 'b'], $mock->values);
        $this->assertSame(['a', 'b'], unserialize(serialize($mock))->values);

        $handle->__serialize->returns(['c', 'd']);

        $this->assertSame(['c', 'd'], unserialize(serialize($mock))->values);
    }

    public function testMockWithToStringExceptions()
    {
        $handle = mock(TestClassWithToStringException::class);
        $mock = $handle->get();
        $expected = new RuntimeException('Exception message');
        $handle->__toString->throws($expected);

        $this->expectExceptionObject($expected);
        strval($mock);
    }

    public function testPartialMockWithToStringExceptions()
    {
        $mock = partialMock(TestClassWithToStringException::class)->get();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You done goofed');
        strval($mock);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testCanMockEnumReturnType()
    {
        $handle = mock(TestInterfaceUsingEnums::class);
        $staticHandle = onStatic($handle);
        $mock = $handle->get();
        $class = $staticHandle->className();

        $this->assertSame(TestBasicEnum::A, $mock->methodA());
        $this->assertSame(TestBasicEnum::A, $mock->methodB(TestBasicEnum::B));
        $this->assertSame(TestBackedEnum::A, $mock->methodC());
        $this->assertSame(TestBackedEnum::A, $mock->methodD(TestBackedEnum::B));
        $this->assertSame(TestBasicEnum::A, $class::staticMethodA());
        $this->assertSame(TestBasicEnum::A, $class::staticMethodB(TestBasicEnum::B));
        $this->assertSame(TestBackedEnum::A, $class::staticMethodC());
        $this->assertSame(TestBackedEnum::A, $class::staticMethodD(TestBackedEnum::B));

        $handle->methodA->returns(TestBasicEnum::B);
        $handle->methodB->returns(TestBasicEnum::B);
        $handle->methodC->returns(TestBackedEnum::C);
        $handle->methodD->returns(TestBackedEnum::C);
        $staticHandle->staticMethodA->returns(TestBasicEnum::B);
        $staticHandle->staticMethodB->returns(TestBasicEnum::B);
        $staticHandle->staticMethodC->returns(TestBackedEnum::C);
        $staticHandle->staticMethodD->returns(TestBackedEnum::C);

        $this->assertSame(TestBasicEnum::B, $mock->methodA());
        $this->assertSame(TestBasicEnum::B, $mock->methodB(TestBasicEnum::A));
        $this->assertSame(TestBackedEnum::C, $mock->methodC());
        $this->assertSame(TestBackedEnum::C, $mock->methodD(TestBackedEnum::A));
        $this->assertSame(TestBasicEnum::B, $class::staticMethodA());
        $this->assertSame(TestBasicEnum::B, $class::staticMethodB(TestBasicEnum::A));
        $this->assertSame(TestBackedEnum::C, $class::staticMethodC());
        $this->assertSame(TestBackedEnum::C, $class::staticMethodD(TestBackedEnum::A));
    }

    public function testCanMockConstructorProperties()
    {
        $handle = partialMock(TestClassWithConstructorProperties::class, [111, 222, 333]);
        $mock = $handle->get();

        $this->assertSame(111, $mock->publicConstructor);
        $this->assertSame(222, $mock->protectedConstructor);
        $this->assertSame(333, $mock->privateConstructor);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testCanMockReadonlyProperties()
    {
        $handle = partialMock(TestClassWithReadonlyProperties::class, [111, 222, 333]);
        $mock = $handle->get();

        $this->assertSame(1, $mock->public);
        $this->assertSame(2, $mock->protected);
        $this->assertSame(3, $mock->private);
        $this->assertSame(111, $mock->publicConstructor);
        $this->assertSame(222, $mock->protectedConstructor);
        $this->assertSame(333, $mock->privateConstructor);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testCanMockReadonlyClass()
    {
        $handle = partialMock(TestClassReadonly::class);
        $mock = $handle->get();

        $this->assertSame('a', $mock->propertyA);
        $this->assertSame(111, $mock->propertyB);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testCanMockReadonlySubclass()
    {
        $handle = partialMock(TestClassReadonlySubclass::class);
        $mock = $handle->get();

        $this->assertSame('a', $mock->propertyA);
        $this->assertSame(111, $mock->propertyB);
        $this->assertTrue($mock->propertyC);
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testCanMockPhp82StandaloneTypes()
    {
        $handle = mock(TestInterfaceWithPhp82StandaloneTypes::class);
        $staticHandle = onStatic($handle);
        $mock = $handle->get();
        $class = $staticHandle->className();

        $this->assertTrue($mock->methodA(true));
        $this->assertFalse($mock->methodB(false));
        $this->assertNull($mock->methodC(null));
        $this->assertTrue($class::staticMethodA(true));
        $this->assertFalse($class::staticMethodB(false));
        $this->assertNull($class::staticMethodC(null));

        $handle->methodA->returns(true);
        $handle->methodB->returns(false);
        $handle->methodC->returns(null);
        $staticHandle->staticMethodA->returns(true);
        $staticHandle->staticMethodB->returns(false);
        $staticHandle->staticMethodC->returns(null);

        $this->assertTrue($mock->methodA(true));
        $this->assertFalse($mock->methodB(false));
        $this->assertNull($mock->methodC(null));
        $this->assertTrue($class::staticMethodA(true));
        $this->assertFalse($class::staticMethodB(false));
        $this->assertNull($class::staticMethodC(null));
    }

    public function testCanMockUnionTypes()
    {
        $handle = mock(TestInterfaceWithUnionTypes::class);
        $staticHandle = onStatic($handle);
        $mock = $handle->get();
        $class = $staticHandle->className();

        $this->assertSame(0, $mock->methodA(111));
        $this->assertSame(0, $mock->methodA('a'));
        $this->assertSame(0, $class::staticMethodA(111));
        $this->assertSame(0, $class::staticMethodA('a'));

        $handle->methodA->returns('x');
        $staticHandle->staticMethodA->returns('y');

        $this->assertSame('x', $mock->methodA(111));
        $this->assertSame('y', $class::staticMethodA(111));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testCanMockIntersectionTypes()
    {
        $handle = mock(TestInterfaceWithIntersectionTypes::class);
        $staticHandle = onStatic($handle);
        $mock = $handle->get();
        $class = $staticHandle->className();

        $countableIteratorA = new TestCountableIterator();
        $returnValueA = $mock->methodA($countableIteratorA);
        $returnValueB = $class::staticMethodA($countableIteratorA);

        $this->assertInstanceOf(Countable::class, $returnValueA);
        $this->assertInstanceOf(Iterator::class, $returnValueA);
        $this->assertInstanceOf(Countable::class, $returnValueB);
        $this->assertInstanceOf(Iterator::class, $returnValueB);

        $countableIteratorB = new TestCountableIterator();
        $countableIteratorC = new TestCountableIterator();
        $handle->methodA->returns($countableIteratorB);
        $staticHandle->staticMethodA->returns($countableIteratorC);

        $this->assertSame($countableIteratorB, $mock->methodA($countableIteratorA));
        $this->assertSame($countableIteratorC, $class::staticMethodA($countableIteratorA));
    }

    /**
     * @requires PHP >= 8.2
     */
    public function testCanMockDnfTypes()
    {
        $handle = mock(TestInterfaceWithDnfTypes::class);
        $staticHandle = onStatic($handle);
        $mock = $handle->get();
        $class = $staticHandle->className();

        $countableIteratorA = new TestCountableIterator();
        $returnValueA = $mock->methodA($countableIteratorA);
        $returnValueB = $class::staticMethodA($countableIteratorA);

        $this->assertInstanceOf(Countable::class, $returnValueA);
        $this->assertInstanceOf(IteratorAggregate::class, $returnValueA);
        $this->assertInstanceOf(Countable::class, $returnValueB);
        $this->assertInstanceOf(IteratorAggregate::class, $returnValueB);

        $countableIteratorB = new TestCountableIterator();
        $countableIteratorC = new TestCountableIterator();
        $handle->methodA->returns($countableIteratorB);
        $staticHandle->staticMethodA->returns($countableIteratorC);

        $this->assertSame($countableIteratorB, $mock->methodA($countableIteratorA));
        $this->assertSame($countableIteratorC, $class::staticMethodA($countableIteratorA));
    }

    public function testCanMockWithIntersectionTypeNotation()
    {
        $handle = mock('Countable&Iterator');
        $mock = $handle->get();

        $this->assertInstanceOf(Countable::class, $mock);
        $this->assertInstanceOf(Iterator::class, $mock);
    }

    public function testCanMockStringable()
    {
        $handle = mock(Stringable::class);
        $mock = $handle->get();

        $this->assertInstanceOf(Stringable::class, $mock);
        $this->assertSame('', (string) $mock);
    }

    /**
     * @see https://github.com/eloquent/phony/issues/268
     */
    public function testCanMockTraitsWithAbstractSelfMethods()
    {
        $handle = mock([TestTraitWithSelfType::class, IteratorAggregate::class]);
        $mock = $handle->get();
        $staticHandle = onStatic($handle);
        $class = $staticHandle->className();

        $this->assertInstanceOf(IteratorAggregate::class, $mock);
        $this->assertInstanceOf(IteratorAggregate::class, $mock->method($mock));
        $this->assertInstanceOf(IteratorAggregate::class, $class::staticMethod($mock));
    }

    public function testCanMockSelfTypes()
    {
        $handle = mock(TestInterfaceWithSelfReturnType::class);
        $mock = $handle->get();
        $staticHandle = onStatic($handle);
        $class = $staticHandle->className();

        $this->assertInstanceOf(TestInterfaceWithSelfReturnType::class, $mock->method());
        $this->assertInstanceOf(TestInterfaceWithSelfReturnType::class, $class::staticMethod());
    }

    public function testCanMockStaticTypes()
    {
        $handle = mock(TestInterfaceWithStaticReturnType::class);
        $mock = $handle->get();
        $staticHandle = onStatic($handle);
        $class = $staticHandle->className();

        $this->assertInstanceOf($class, $mock->method());
        $this->assertInstanceOf($class, $class::staticMethod());
    }

    public function testCanCallMocksWithNamedArguments()
    {
        $handle = mock(TestClassA::class);
        $mock = $handle->get();
        $mock->testClassAMethodB(second: '<second>', first: '<first>');

        $this->assertTrue((bool) $handle->testClassAMethodB->calledWith('<first>', '<second>'));
    }

    public function testCanCallMocksWithVariadicNamedArguments()
    {
        $mock = partialMock(TestClassWithVariadicNamedArguments::class)->get();
        $mock->setArguments(a: 'x', b: 'y');

        $this->assertSame(['a' => 'x', 'b' => 'y'], $mock->arguments);
    }
}
