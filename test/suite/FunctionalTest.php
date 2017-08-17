<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony as x;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestInvocable;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    protected function setUp()
    {
        $this->featureDetector = FeatureDetector::instance();
        $this->exporter = InlineExporter::instance();

        x\setUseColor(false);
    }

    public function testMockingStatic()
    {
        $handle = Phony::partialMock('Eloquent\Phony\Test\TestClassA');
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
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassA');
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
        $mock = x\partialMock('Eloquent\Phony\Test\TestClassB', ['A', 'B'])->get();
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
        $mock = x\partialMock('Eloquent\Phony\Test\TestClassB')->get();

        $this->assertSame('static magic nonexistent ab', $mock::nonexistent('a', 'b'));
        $this->assertSame('magic nonexistent ab', $mock->nonexistent('a', 'b'));

        x\onStatic($mock)->nonexistent->with('c', 'd')->returns('x');
        x\on($mock)->nonexistent->with('c', 'd')->returns('z');

        $this->assertSame('x', $mock::nonexistent('c', 'd'));
        $this->assertSame('static magic nonexistent ef', $mock::nonexistent('e', 'f'));
        $this->assertSame('z', $mock->nonexistent('c', 'd'));
        $this->assertSame('magic nonexistent ef', $mock->nonexistent('e', 'f'));
    }

    public function testMockMocking()
    {
        $mock = x\partialMock();
        $mockMock = x\partialMock($mock->className());

        $this->assertInstanceOf(get_class($mock->get()), $mockMock->get());
        $this->assertNotInstanceOf(get_class($mockMock->get()), $mock->get());
    }

    public function testVariadicParameterMocking()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithVariadicParameter');
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );

        $this->assertSame([1, 2], $handle->get()->method(1, 2));
    }

    public function testVariadicParameterMockingWithType()
    {
        if (!$this->featureDetector->isSupported('parameter.variadic.type')) {
            $this->markTestSkipped('Requires type hint support for variadic parameters.');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithVariadicParameterWithType');
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );
        $a = (object) [];
        $b = (object) [];

        $this->assertSame([$a, $b], $handle->get()->method($a, $b));
    }

    public function testVariadicParameterMockingByReference()
    {
        if (!$this->featureDetector->isSupported('parameter.variadic.reference')) {
            $this->markTestSkipped('Requires by-reference variadic parameters.');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithVariadicParameterByReference');
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
        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint');
        $handle->get()->method(123, 1.23, '<string>', true);

        $this->assertTrue((bool) $handle->method->calledWith(123, 1.23, '<string>', true));
    }

    public function testReturnTypeMocking()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithReturnType');
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
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $handle->get()->classType());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $handle->get()->classType());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\InstanceHandle', x\on($handle->get()->classType()));
        $this->assertSame(123, $handle->get()->scalarType('x'));
        $this->assertSame(0, $handle->get()->scalarType());
    }

    public function testMagicMethodReturnTypeMocking()
    {
        $mock = x\mock('Eloquent\Phony\Test\TestInterfaceWithReturnType')->get();

        x\onStatic($mock)->nonexistent->returns('x');
        x\on($mock)->nonexistent->returns('z');

        $this->assertSame('x', $mock::nonexistent());
        $this->assertSame('z', $mock->nonexistent());
    }

    public function testGeneratorReturnTypeSpying()
    {
        $stub = x\stub(eval('return function (): Generator {};'))->returns();
        iterator_to_array($stub());

        $this->assertTrue((bool) $stub->generated());
    }

    public function testReturnTypeMockingInvalidType()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithReturnType');
        $handle->scalarType->returns('<string>');

        $this->expectException('TypeError');
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
        $spy = x\spy();
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
            (bool) x\inOrder(
                $spy->calledWith('a', 'b', 'c'),
                $spy->calledWith(111)
            )
        );
    }

    public function testSpyReturnType()
    {
        $spy = x\spy(eval('return function () : int { return 123; };'));

        $this->assertSame(123, $spy());
    }

    public function testSpyGlobal()
    {
        $stubA = x\spyGlobal('vsprintf', 'Eloquent\Phony\Test');

        $this->assertSame('a, b', Test\vsprintf('%s, %s', ['a', 'b']));
        $this->assertTrue((bool) $stubA->calledWith('%s, %s', ['a', 'b']));

        $stubB = x\spyGlobal('vsprintf', 'Eloquent\Phony\Test');

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
        $stub = x\stub()
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
            (bool) x\inOrder(
                $stub->calledWith('a', 'b', 'c'),
                $stub->returned('x'),
                $stub->calledWith(111),
                $stub->returned('y')
            )
        );
    }

    public function testStubMagicSelf()
    {
        $callback = function ($phonySelf) {
            return $phonySelf;
        };

        $stub = x\stub($callback)->forwards();

        $this->assertSame($callback, $stub());
    }

    public function testStubReturnType()
    {
        $stub = x\stub(eval('return function () : int { return 123; };'))->forwards();

        $this->assertSame(123, $stub());
    }

    public function testStubGlobal()
    {
        $stubA = x\stubGlobal('vsprintf', 'Eloquent\Phony\Test');

        $this->assertNull(Test\vsprintf('%s, %s', ['a', 'b']));

        $stubA->returns('x');

        $this->assertSame('x', Test\vsprintf('%s, %s', ['a', 'b']));

        $stubA->forwards();

        $this->assertSame('a, b', Test\vsprintf('%s, %s', ['a', 'b']));
        $stubA->times(3)->calledWith('%s, %s', ['a', 'b']);

        $stubB = x\stubGlobal('vsprintf', 'Eloquent\Phony\Test');

        $this->assertNull(Test\vsprintf('%s, %s', ['a', 'b']));
        $stubB->calledWith('%s, %s', ['a', 'b']);

        $stubB->returns('x');

        $this->assertSame('x', Test\vsprintf('%s, %s', ['a', 'b']));

        x\restoreGlobalFunctions();

        $this->assertSame('a, b', Test\vsprintf('%s, %s', ['a', 'b']));
    }

    public function testIterableSpying()
    {
        $value = ['a' => 'b', 'c' => 'd'];

        $stub = x\stub();
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
        $this->assertSame(2, count($result));
    }

    public function testIterableSpyingWithArrayLikeObject()
    {
        $value = ['a' => 'b', 'c' => 'd'];

        $stub = x\stub();
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
        $this->assertSame(2, count($result));
    }

    public function testDefaultStubAnswerCanBeOverridden()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->with('a', 'b')->returns(123);
        $mock = $handle->get();

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));
        $this->assertSame('ef', $mock->testClassAMethodB('e', 'f'));
    }

    public function testFullMockDefaultStubAnswerCanBeOverridden()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
        $mock = $handle->get();
        $handle->testClassAMethodA->with('a', 'b')->returns(123);

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertNull($mock->testClassAMethodA('c', 'd'));
        $this->assertNull($mock->testClassAMethodB('e', 'f'));
    }

    public function testMagicMockDefaultStubAnswerCanBeOverridden()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassB');
        $mock = $handle->get();
        $handle->nonexistentA->with('a', 'b')->returns(123);

        $this->assertSame(123, $mock->nonexistentA('a', 'b'));
        $this->assertNull($mock->nonexistentA('c', 'd'));
        $this->assertNull($mock->nonexistentB('e', 'f'));
    }

    public function testDoesntCallParentOnInterfaceOnlyMock()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestInterfaceA');
        $mock = $handle->get();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));
    }

    public function testDefaultArgumentsNotRecorded()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassC');
        $handle->get()->methodB('a');

        $this->assertTrue((bool) $handle->methodB->calledWith('a'));
    }

    public function testHandleStubOverriding()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->returns('x');
        $handle->testClassAMethodA->returns('y', 'z');

        $this->assertSame('y', $handle->get()->testClassAMethodA());
        $this->assertSame('z', $handle->get()->testClassAMethodA());
        $this->assertSame('z', $handle->get()->testClassAMethodA());
    }

    public function testCanCallMockedInterfaceMethod()
    {
        $handle = x\partialMock(['stdClass', 'Eloquent\Phony\Test\TestInterfaceA']);

        $this->assertNull($handle->get()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedInterfaceMethodWithoutParentClass()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestInterfaceA');

        $this->assertNull($handle->get()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethod()
    {
        $handle = x\partialMock(['stdClass', 'Eloquent\Phony\Test\TestTraitA']);

        $this->assertSame('ab', $handle->get()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithoutParentClass()
    {
        $handle = x\partialMock(['Eloquent\Phony\Test\TestTraitA']);

        $this->assertSame('ab', $handle->get()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethod()
    {
        $handle = x\partialMock(['stdClass', 'Eloquent\Phony\Test\TestTraitC']);

        $this->assertNull($handle->get()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethodWithoutParentClass()
    {
        $handle = x\partialMock(['Eloquent\Phony\Test\TestTraitC']);

        $this->assertNull($handle->get()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithInterface()
    {
        $handle = x\partialMock(['Eloquent\Phony\Test\TestTraitH', 'Eloquent\Phony\Test\TestInterfaceE']);

        $this->assertSame('a', $handle->get()->methodA());
    }

    public function testCanMockClassWithPrivateConstructor()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassD');

        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassD', $handle->get());
    }

    public function testCanMockTraitWithPrivateConstructor()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitF', ['a', 'b']);

        $this->assertSame(['a', 'b'], $handle->get()->constructorArguments);
    }

    public function testCanMockClassAndCallPrivateConstructor()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassD', ['a', 'b']);

        $this->assertSame(['a', 'b'], $handle->get()->constructorArguments);
    }

    public function testMatcherAdaptationForBooleanValues()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->with(true)->returns('a');

        $this->assertNull($handle->get()->testClassAMethodA());
    }

    public function testAssertionExceptionTrimming()
    {
        $spy = x\spy();
        $exception = null;

        try {
            $line = __LINE__ + 1;
            $spy->called();
        } catch (Exception $exception) {
        }

        $this->assertInstanceOf('Exception', $exception);
        $this->assertSame(__FILE__, $exception->getFile());
        $this->assertSame($line, $exception->getLine());
        $this->assertSame(
            [
                [
                    'file' => __FILE__,
                    'line' => $line,
                    'function' => 'called',
                    'class' => 'Eloquent\Phony\Spy\SpyVerifier',
                    'type' => '->',
                    'args' => [],
                ],
            ],
            $exception->getTrace()
        );
    }

    public function testAssertionExceptionTrimmingWithEmptyTrace()
    {
        $exception = new Exception();
        $reflector = new ReflectionClass('Exception');
        $traceProperty = $reflector->getProperty('trace');
        $traceProperty->setAccessible(true);
        $traceProperty->setValue($exception, []);
        AssertionException::trim($exception);

        $this->assertNull($exception->getFile());
        $this->assertNull($exception->getLine());
        $this->assertSame([], $exception->getTrace());
    }

    public function testHandleCaseInsensitivity()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassA');

        $this->assertSame($handle->testClassAMethodA, $handle->testclassamethoda);
    }

    public function testIterableInterfaceMocking()
    {
        x\partialMock('Eloquent\Phony\Test\TestInterfaceC');

        $this->assertTrue(true);
    }

    public function testIterableInterfaceMockingWithPDOStatement()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestIncomplete('Broken under HHVM.');
        }

        $this->assertInstanceOf('PDOStatement', x\mock('PDOStatement')->get());
    }

    public function testTraitConstructorCalling()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitD', ['a', 'b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $handle->get()->constructorArguments);
    }

    public function testTraitConstructorConflictResolution()
    {
        $handle = x\partialMock(
            ['Eloquent\Phony\Test\TestTraitD', 'Eloquent\Phony\Test\TestTraitE'],
            ['a', 'b', 'c']
        );

        $this->assertSame(['a', 'b', 'c'], $handle->get()->constructorArguments);
    }

    public function testCallAtWithAssertionResult()
    {
        $spy = x\spy();
        $spy('a', 1);
        $spy('b', 1);
        $spy('a', 2);

        $this->assertSame(['a', 2], $spy->calledWith('a', '*')->callAt(1)->arguments()->all());
    }

    public function testPhonySelfMagicParameter()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
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
        $spy = x\spy();
        $spy('a');
        $spy('b');
        $spy('c');
        $spy('d');

        $this->assertTrue(
            (bool) x\inOrder(
                $spy->calledWith('a'),
                x\anyOrder(
                    $spy->calledWith('c'),
                    $spy->calledWith('b')
                ),
                $spy->calledWith('d')
            )
        );
    }

    public function testCanForwardAfterFullMock()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
        $mock = $handle->get();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));

        $handle->testClassAMethodA->returns('x');

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));

        $handle->testClassAMethodA->forwards();

        $this->assertSame('ab', $mock->testClassAMethodA('a', 'b'));
    }

    public function testCanForwardToMagicCallAfterFullMock()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassB');
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
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassB');
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
        $handle = x\mock('Exception');

        $this->assertInstanceOf('Exception', $handle->get());
    }

    public function testMockMethodAssertionRenderingWithRealMethod()
    {
        $mock =
            x\mockBuilder('Eloquent\Phony\Test\TestClassA')->named('PhonyMockAssertionRenderingWithRealMethod')->get();
        $handle = x\on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->testClassAMethodA->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertContains(
            'Expected TestClassA[label]->testClassAMethodA call with arguments',
            $error->getMessage()
        );
    }

    public function testMockMethodAssertionRenderingWithMagicMethod()
    {
        $mock =
            x\mockBuilder('Eloquent\Phony\Test\TestClassB')->named('PhonyMockAssertionRenderingWithMagicMethod')->get();
        $handle = x\on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->magicMethod->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertContains(
            'Expected TestClassB[label]->magicMethod call with arguments',
            $error->getMessage()
        );
    }

    public function testMockMethodAssertionRenderingWithUncallableMethod()
    {
        $mock = x\mockBuilder('IteratorAggregate')->named('PhonyMockAssertionRenderingWithUncallableMethod')->get();
        $handle = x\on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->getIterator->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertContains(
            'Expected IteratorAggregate[label]->getIterator call with arguments',
            $error->getMessage()
        );
    }

    public function testMockMethodAssertionRenderingWithCustomMethod()
    {
        $mock = x\mockBuilder()->named('PhonyMockAssertionRenderingWithCustomMethod')->addMethod('customMethod')->get();
        $handle = x\on($mock);
        $handle->setLabel('label');

        $error = null;

        try {
            $handle->customMethod->calledWith('a');
        } catch (Exception $error) {
        }

        $this->assertNotNull($error);
        $this->assertContains(
            'Expected PhonyMockAssertionRenderingWithCustomMethod[label]->customMethod call with arguments',
            $error->getMessage()
        );
    }

    public function testCanCallCustomMethodWithInvocableObjectImplementation()
    {
        $mock = x\partialMock(['methodA' => new TestInvocable()])->get();

        $this->assertSame(['invokeWith', ['a', 'b']], $mock->methodA('a', 'b'));
    }

    public function testMockWithUncallableMagicMethod()
    {
        $mock = x\mock('Eloquent\Phony\Test\TestInterfaceD')->get();

        $this->assertNull($mock->nonexistent());
    }

    public function testNoInteraction()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceD');

        $this->assertTrue((bool) $handle->noInteraction());
    }

    public function testCallsArgumentWithFullMockImplicitReturns()
    {
        $handle = Phony::mock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->callsArgument(0);
        $spy = Phony::spy();

        $this->assertNull($handle->get()->testClassAMethodA($spy));
        $this->assertTrue((bool) $spy->called());
    }

    public function testIncompleteCalls()
    {
        $test = $this;
        $context = (object) ['spy' => null];
        $context->spy = $spy = x\spy(
            function () use ($test, $context) {
                $test->assertFalse($context->spy->callAt(0)->hasResponded());
                $test->assertFalse($context->spy->callAt(0)->hasCompleted());
            }
        );

        $spy();
    }

    public function testCallRespondedAndCompleted()
    {
        $stub = x\stub();
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
        $instance = eval('return new class {};');

        $this->expectException('Eloquent\Phony\Mock\Exception\AnonymousClassException');
        x\mock(get_class($instance));
    }

    public function testPartialMockOfMagicCallTrait()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitJ');
        $mock = $handle->get();

        $this->assertSame('magic a bc', $mock->a('b', 'c'));
        $this->assertTrue((bool) $handle->a->calledWith('b', 'c'));
    }

    public function testPartialMockOfStaticMagicCallTrait()
    {
        $mock = x\partialMock('Eloquent\Phony\Test\TestTraitJ')->get();
        $class = get_class($mock);

        $this->assertSame('magic a bc', $class::a('b', 'c'));
        $this->assertTrue((bool) x\onStatic($mock)->a->calledWith('b', 'c'));
    }

    public function testInvalidStubUsageWithInvoke()
    {
        $stub = x\stub()->with();

        $this->expectException('Eloquent\Phony\Stub\Exception\UnusedStubCriteriaException');
        $stub();
    }

    public function testMockHandleSubstitution()
    {
        $handleA = x\mock();
        $handleA->get();
        $handleB = x\mock(['methodA' => function () {}]);
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
        $stub = x\stub()->setUseIterableSpies(true)->returnsArgument();
        $iterable = ['a', 'b'];
        $iterableSpy = $stub($iterable);
        $spy = x\spy();
        $spy($iterable);
        $spy($iterableSpy);

        $this->assertTrue((bool) $stub->returned($iterable));
        $this->assertTrue((bool) $stub->returned($iterableSpy));
        $this->assertTrue((bool) $stub->returned(x\equalTo($iterable)));
        $this->assertTrue((bool) $stub->never()->returned(x\equalTo($iterableSpy)));
        $this->assertTrue((bool) $spy->callAt(0)->calledWith($iterable));
        $this->assertTrue((bool) $spy->callAt(0)->calledWith($iterableSpy));
        $this->assertTrue((bool) $spy->callAt(0)->never()->calledWith(x\equalTo($iterableSpy)));
        $this->assertTrue((bool) $spy->callAt(1)->calledWith($iterable));
        $this->assertTrue((bool) $spy->callAt(1)->calledWith($iterableSpy));
        $this->assertTrue((bool) $spy->callAt(1)->never()->calledWith(x\equalTo($iterable)));
    }

    public function testReturnByReferenceMocking()
    {
        $a = 'a';
        $b = 'b';
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassG');
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
        $foo = x\partialMock(['test' => function () { return 'foo'; }])->get();
        $bar = x\partialMock(['test' => function () { return 'bar'; }])->get();

        $this->assertSame('foo', $foo->test());
        $this->assertSame('bar', $bar->test());
    }

    public function testAdHocMocksWithMagicSelf()
    {
        $mock = x\partialMock(['test' => function ($phonySelf) { return $phonySelf; }])->get();

        $this->assertSame($mock, $mock->test());
    }

    public function testAdHocMocksWithMagicSelfOutput()
    {
        $builder = x\mockBuilder(['test' => function ($phonySelf) { return $phonySelf; }])
            ->named('PhonyTestAdHocMocksWithMagicSelfOutput');
        $mock = $builder->get();
        $handle = x\on($mock)->setLabel('label');

        $this->expectException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'PhonyTestAdHocMocksWithMagicSelfOutput[label]->test'
        );
        $handle->test->calledWith('a');
    }

    public function testBasicGeneratorStubbing()
    {
        $stub = x\stub()
            ->generates(['a' => 'b', 'c'])
                ->yields('d', 'e')
                ->yields('f')
                ->yields()
                ->returns();

        $generator = $stub();
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame(['a' => 'b', 0 => 'c', 'd' => 'e', 1 => 'f', 2 => null], $actual);
    }

    public function testGeneratorStubbingWithReturnValue()
    {
        $stub = x\stub()->generates()->returns('d');

        $generator = $stub();
        iterator_to_array($generator);

        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame('d', $generator->getReturn());
    }

    public function testGeneratorStubbingWithMultipleAnswers()
    {
        $stub = x\stub()
            ->generates()->yields('a')->returns()
            ->returns('b')
            ->generates()->yields('c')->returns();

        $this->assertSame(['a'], iterator_to_array($stub()));
        $this->assertSame('b', $stub());
        $this->assertSame(['c'], iterator_to_array($stub()));
    }

    public function testGeneratorStubbingWithEmptyGenerator()
    {
        $stub = x\stub();
        $stub->generates();

        $generator = $stub();
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame([], $actual);
    }

    public function testAssertionExceptionConstruction()
    {
        $actual = new AssertionException('You done goofed.');

        $this->assertNotNull($actual);
    }

    public function testFinalConstructorBypass()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassI');
        $mock = $handle->get();

        $this->assertNull($mock->constructorArguments);
    }

    public function testIterableSpyDoubleWrappingWithArray()
    {
        $stub = x\stub()->setUseIterableSpies(true)->returns(['a', 'b']);
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
        $stub = x\stub()->setUseIterableSpies(true)->returns(new ArrayIterator(['a', 'b']));
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

    public function testIterableSpyDoubleWrappingWithGenerator()
    {
        $stub = x\stub()->generates()->yieldsFrom(['a', 'b', 'c'])->returns();
        $generatorSpyA = $stub();
        $stub->returns($generatorSpyA);
        $generatorSpyB = $stub();

        $this->assertSame($generatorSpyA, $generatorSpyB->_phonySubject);

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
        $inner = new ClassA();
        $inner->c = 'd';
        $classNameExclusion = (object) ['a' => $inner, 'b' => $inner];

        return [
            // The exporter format
            'null'           => [null,                               'null'],
            'true'           => [true,                               'true'],
            'false'          => [false,                              'false'],
            'integer'        => [111,                                '111'],
            'float'          => [1.11,                               '1.110000e+0'],
            'float string'   => ['1.11',                             '"1.11"'],
            'string'         => ["a\nb",                             '"a\nb"'],
            'resource'       => [STDIN,                              'resource#1'],
            'sequence'       => [$sequence,                          '#0[1, 2]'],
            'map'            => [['a' => 1, 'b' => 2],          '#0["a": 1, "b": 2]'],
            'generic object' => [(object) ['a' => 1, 'b' => 2], '#0{a: 1, b: 2}'],
            'object'         => [new ClassA(),                       'ClassA#0{}'],

            // Export identifiers and references
            'repeated sequence'       => [$repeatedSequences, '#0[#1[1, 2], &1[]]'],
            'repeated generic object' => [$repeatedObjects,   '#0{b: #1{a: 1}, c: &1{}}'],

            // Export reference types
            'identifier collision' => [$identifierCollision, '#0[#0{}, #1[#1{}]]'],

            // Export reference exclusions
            'class name exclusion' => [$classNameExclusion, '#0{a: ClassA#1{c: "d"}, b: &1{}}'],

            // Exporting closures
            'closure' => [function () {}, 'Closure#0{}[FunctionalTest.php:' . __LINE__ . ']'],

            // Exporting exceptions
            'exception'           => [new Exception('a', 1, new Exception()), 'Exception#0{message: "a", code: 1, previous: Exception#1{}}'],
            'exception defaulted' => [new RuntimeException(),                 'RuntimeException#0{}'],
        ];
    }

    /**
     * @dataProvider exporterExamplesTest
     */
    public function testExporterExamples($value, $expected)
    {
        $this->exporter->reset();

        $this->assertSame($expected, $this->exporter->export($value, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesRepeatedWrappers()
    {
        $inner = x\mock('ClassA')->setLabel('mock-label');
        $value = [$inner, $inner];
        $this->exporter->reset();

        $this->assertSame(
            '#0[handle#0(PhonyMock_ClassA_0#1{}[mock-label]), &0()]',
            $this->exporter->export($value, -1)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesReferenceTypes()
    {
        $array = [];
        $object = (object) [];
        $wrapper = x\spy('implode')->setLabel('spy-label');
        $valueA = [&$array, &$array];
        $valueB = [$object, $object];
        $valueC = [$wrapper, $wrapper];
        $this->exporter->reset();

        $this->assertSame('#0[#1[], &1[]]', $this->exporter->export($valueA, -1));
        $this->assertSame('#0[#0{}, &0{}]', $this->exporter->export($valueB, -1));
        $this->assertSame('#0[spy#1(implode)[spy-label], &1()]', $this->exporter->export($valueC, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesExcludeWrapperValue()
    {
        $inner = x\mock();
        $value = [$inner, $inner];
        $this->exporter->reset();

        $this->assertSame('#0[handle#0(PhonyMock_0#1{}[0]), &0()]', $this->exporter->export($value, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesIdentifierPersistenceObjects()
    {
        $a = (object) [];
        $b = (object) [];
        $c = x\mock();
        $valueA = [$a, $b, $c, $a];
        $valueB = [$b, $a, $b, $c];
        $this->exporter->reset();

        $this->assertSame('#0[#0{}, #1{}, handle#2(PhonyMock_0#3{}[0]), &0{}]', $this->exporter->export($valueA, -1));
        $this->assertSame('#0[#1{}, #0{}, &1{}, handle#2(PhonyMock_0#3{}[0])]', $this->exporter->export($valueB, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesIdentifierPersistenceArrays()
    {
        $a = [];
        $b = [];
        $valueA = [&$a, &$b, &$a];
        $valueB = [&$b, &$a, &$b];
        $this->exporter->reset();

        $this->assertSame('#0[#1[], #2[], &1[]]', $this->exporter->export($valueA, -1));
        $this->assertSame('#0[#1[], #2[], &1[]]', $this->exporter->export($valueB, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesRecursiveValues()
    {
        $recursiveArray = [];
        $recursiveArray[] = &$recursiveArray;
        $recursiveObject = (object) [];
        $recursiveObject->a = $recursiveObject;
        $this->exporter->reset();

        $this->assertSame('#0[&0[]]', $this->exporter->export($recursiveArray, -1));
        $this->assertSame('#0{a: &0{}}', $this->exporter->export($recursiveObject, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesMocks()
    {
        $handle = x\mock('ClassA')->setLabel('mock-label');
        $mock = $handle->get();
        $this->exporter->reset();

        $this->assertSame('PhonyMock_ClassA_0#0{}[mock-label]', $this->exporter->export($mock, -1));
        $this->assertSame('handle#1(PhonyMock_ClassA_0#0{}[mock-label])', $this->exporter->export($handle, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesStaticHandle()
    {
        $handle = x\mock('ClassA')->setLabel('mock-label');
        $staticHandle = x\onStatic($handle);
        $this->exporter->reset();

        $this->assertSame('static-handle#0(PhonyMock_ClassA_0)', $this->exporter->export($staticHandle, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesStubs()
    {
        $stub = x\stub('implode')->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertSame('stub#0(implode)[stub-label]', $this->exporter->export($stub, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesAnonymousStubs()
    {
        $stub = x\stub()->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertSame('stub#0[stub-label]', $this->exporter->export($stub, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesMockStubs()
    {
        $handle = x\mock('ClassA')->setLabel('mock-label');
        $staticHandle = x\onStatic($handle);
        $stubA = $handle->methodA->setLabel('stub-label');
        $stubB = $staticHandle->staticMethodA->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertSame('stub#0(ClassA[mock-label]->methodA)[stub-label]', $this->exporter->export($stubA, -1));
        $this->assertSame('stub#1(ClassA::staticMethodA)[stub-label]', $this->exporter->export($stubB, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesSpies()
    {
        $spy = x\spy('implode')->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertSame('spy#0(implode)[spy-label]', $this->exporter->export($spy, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesAnonymousSpies()
    {
        $spy = x\spy()->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertSame('spy#0[spy-label]', $this->exporter->export($spy, -1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExporterExamplesMethodSpies()
    {
        $object = new ClassA();
        $spyA = x\spy([$object, 'methodA'])->setLabel('spy-label');
        $spyB = x\spy(['ClassA', 'staticMethodA'])->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertSame('spy#0(ClassA->methodA)[spy-label]', $this->exporter->export($spyA, -1));
        $this->assertSame('spy#1(ClassA::staticMethodA)[spy-label]', $this->exporter->export($spyB, -1));
    }

    public function testExporterExamplesExportDepth()
    {
        $valueA = [[], ['a', 'b', 'c']];
        $valueB = [(object) [], (object) ['a', 'b', 'c']];
        $this->exporter->reset();

        $this->assertSame('#0[#1[], #2[~3]]', $this->exporter->export($valueA));
        $this->assertSame('#0[#0{}, #1{~3}]', $this->exporter->export($valueB));
    }

    public function testReturnsVariadic()
    {
        $stub = x\stub()->returns('a')->returns()->returns('b');

        $this->assertSame('a', $stub());
        $this->assertNull($stub());
        $this->assertSame('b', $stub());
    }
}
