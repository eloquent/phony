<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Phpunit as x;
use Eloquent\Phony\Phpunit\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestInvocable;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->featureDetector = FeatureDetector::instance();

        x\setUseColor(false);
    }

    public function testMockingStatic()
    {
        $handle = Phony::partialMock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->with('a', 'b')->returns('x');
        $mock = $handle->mock();

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));

        $this->assertSame(
            array('a', 'b'),
            $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->arguments()->all()
        );
        $this->assertSame('b', $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->argument(1));
    }

    public function testMockingFunctions()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->with('a', 'b')->returns('x');
        $mock = $handle->mock();

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));
        $this->assertSame(
            array('a', 'b'),
            $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->arguments()->all()
        );
        $this->assertSame('b', $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->argument(1));
    }

    public function testMockCalls()
    {
        $mock = x\partialMock('Eloquent\Phony\Test\TestClassB', array('A', 'B'))->mock();
        $e = 'e';
        $n = 'n';
        $q = 'q';
        $r = 'r';

        $this->assertSame(array('A', 'B'), $mock->constructorArguments);
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
        $mock = x\partialMock('Eloquent\Phony\Test\TestClassB')->mock();

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

        $this->assertInstanceOf(get_class($mock->mock()), $mockMock->mock());
        $this->assertNotInstanceOf(get_class($mockMock->mock()), $mock->mock());
    }

    public function testVariadicParameterMocking()
    {
        if (!$this->featureDetector->isSupported('parameter.variadic')) {
            $this->markTestSkipped('Requires variadic parameters.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('Broken because of https://github.com/facebook/hhvm/issues/5762');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithVariadicParameter');
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );

        $this->assertSame(array(1, 2), $handle->mock()->method(1, 2));
    }

    public function testVariadicParameterMockingWithType()
    {
        if (!$this->featureDetector->isSupported('parameter.variadic')) {
            $this->markTestSkipped('Requires variadic parameters.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('Broken because of https://github.com/facebook/hhvm/issues/5762');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithVariadicParameter');
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );
        $a = (object) array();
        $b = (object) array();

        $this->assertSame(array($a, $b), $handle->mock()->method($a, $b));
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
        $handle->mock()->method($a, $b);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testScalarTypeHintMocking()
    {
        if (!$this->featureDetector->isSupported('parameter.hint.scalar')) {
            $this->markTestSkipped('Requires scalar type hints.');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint');

        $handle->mock()->method(123, 1.23, '<string>', true);
        $handle->method->calledWith(123, 1.23, '<string>', true);
    }

    public function testReturnTypeMocking()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

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

        $this->assertSame($object, $handle->mock()->classType('x'));
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $handle->mock()->classType());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $handle->mock()->classType());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\InstanceHandle', x\on($handle->mock()->classType()));
        $this->assertSame(123, $handle->mock()->scalarType('x'));
        $this->assertSame(0, $handle->mock()->scalarType());
    }

    public function testMagicMethodReturnTypeMocking()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $mock = x\mock('Eloquent\Phony\Test\TestInterfaceWithReturnType')->mock();

        x\onStatic($mock)->nonexistent->returns('x');
        x\on($mock)->nonexistent->returns('z');

        $this->assertSame('x', $mock::nonexistent());
        $this->assertSame('z', $mock->nonexistent());
    }

    public function testGeneratorReturnTypeSpying()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }

        $stub = x\stub(eval('return function (): Generator {};'))->returns();
        iterator_to_array($stub());

        $stub->generated();
    }

    public function testReturnTypeMockingInvalidType()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithReturnType');
        $handle->scalarType->returns('<string>');

        $this->setExpectedException('TypeError');
        $handle->mock()->scalarType();
    }

    public function testSpyStatic()
    {
        $spy = Phony::spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $spy->twice()->called();
        $spy->calledWith('a', 'b', 'c');
        $spy->calledWith('a', 'b', '~');
        $spy->calledWith('a', '*');
        $spy->calledWith('*');
        $spy->calledWith(111);
        $spy->calledWith($this->identicalTo('a'), Phony::wildcard($this->anything()));
        $spy->callAt(0)->calledWith('a', 'b', 'c');
        $spy->callAt(1)->calledWith(111);

        Phony::inOrder(
            $spy->calledWith('a', 'b', 'c'),
            $spy->calledWith(111)
        );
    }

    public function testSpyFunction()
    {
        $spy = x\spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $spy->twice()->called();
        $spy->calledWith('a', 'b', 'c');
        $spy->calledWith('a', 'b', '~');
        $spy->calledWith('a', '*');
        $spy->calledWith('*');
        $spy->calledWith(111);
        $spy->calledWith($this->identicalTo('a'), x\wildcard($this->anything()));
        $spy->callAt(0)->calledWith('a', 'b', 'c');
        $spy->callAt(1)->calledWith(111);

        x\inOrder(
            $spy->calledWith('a', 'b', 'c'),
            $spy->calledWith(111)
        );
    }

    public function testSpyReturnType()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $spy = x\spy(eval('return function () : int { return 123; };'));

        $this->assertSame(123, $spy());
    }

    public function testSpyGlobal()
    {
        $stubA = x\spyGlobal('vsprintf', 'Eloquent\Phony\Test');

        $this->assertSame('a, b', Test\vsprintf('%s, %s', array('a', 'b')));
        $stubA->calledWith('%s, %s', array('a', 'b'));

        $stubB = x\spyGlobal('vsprintf', 'Eloquent\Phony\Test');

        $this->assertSame('a, b', Test\vsprintf('%s, %s', array('a', 'b')));
        $stubB->calledWith('%s, %s', array('a', 'b'));
    }

    public function testStubStatic()
    {
        $stub = Phony::stub()
            ->returns('x')
            ->with(111)->returns('y');

        $this->assertSame('x', $stub('a', 'b', 'c'));
        $this->assertSame('y', $stub(111));
        $stub->twice()->called();
        $stub->calledWith('a', 'b', 'c');
        $stub->calledWith('a', 'b', '~');
        $stub->calledWith('a', '*');
        $stub->calledWith('*');
        $stub->calledWith(111);
        $stub->calledWith($this->identicalTo('a'), Phony::wildcard($this->anything()));
        $stub->callAt(0)->calledWith('a', 'b', 'c');
        $stub->callAt(1)->calledWith(111);
        $stub->returned('x');
        $stub->returned('y');

        Phony::inOrder(
            $stub->calledWith('a', 'b', 'c'),
            $stub->returned('x'),
            $stub->calledWith(111),
            $stub->returned('y')
        );
    }

    public function testStubFunction()
    {
        $stub = x\stub()
            ->returns('x')
            ->with(111)->returns('y');

        $this->assertSame('x', $stub('a', 'b', 'c'));
        $this->assertSame('y', $stub(111));
        $stub->twice()->called();
        $stub->calledWith('a', 'b', 'c');
        $stub->calledWith('a', 'b', '~');
        $stub->calledWith('a', '*');
        $stub->calledWith('*');
        $stub->calledWith(111);
        $stub->calledWith($this->identicalTo('a'), x\wildcard($this->anything()));
        $stub->callAt(0)->calledWith('a', 'b', 'c');
        $stub->callAt(1)->calledWith(111);
        $stub->returned('x');
        $stub->returned('y');

        x\inOrder(
            $stub->calledWith('a', 'b', 'c'),
            $stub->returned('x'),
            $stub->calledWith(111),
            $stub->returned('y')
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
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }

        $stub = x\stub(eval('return function () : int { return 123; };'))->forwards();

        $this->assertSame(123, $stub());
    }

    public function testStubGlobal()
    {
        $stubA = x\stubGlobal('vsprintf', 'Eloquent\Phony\Test');

        $this->assertNull(Test\vsprintf('%s, %s', array('a', 'b')));

        $stubA->returns('x');

        $this->assertSame('x', Test\vsprintf('%s, %s', array('a', 'b')));

        $stubA->forwards();

        $this->assertSame('a, b', Test\vsprintf('%s, %s', array('a', 'b')));
        $stubA->times(3)->calledWith('%s, %s', array('a', 'b'));

        $stubB = x\stubGlobal('vsprintf', 'Eloquent\Phony\Test');

        $this->assertNull(Test\vsprintf('%s, %s', array('a', 'b')));
        $stubB->calledWith('%s, %s', array('a', 'b'));

        $stubB->returns('x');

        $this->assertSame('x', Test\vsprintf('%s, %s', array('a', 'b')));

        x\restoreGlobalFunctions();

        $this->assertSame('a, b', Test\vsprintf('%s, %s', array('a', 'b')));
    }

    public function testIterableSpying()
    {
        $value = array('a' => 'b', 'c' => 'd');

        $stub = x\stub();
        $stub->setUseIterableSpies(true);
        $stub->returns($value);
        $result = $stub();

        $this->assertSame($value, iterator_to_array($result));
        $this->assertSame($value, iterator_to_array($result));

        $stub->iterated()->produced();
        $stub->iterated()->produced('b');
        $stub->iterated()->produced('d');
        $stub->iterated()->produced('a', 'b');
        $stub->iterated()->produced('c', 'd');

        $this->assertSame('b', $result['a']);
        $this->assertSame(2, count($result));
    }

    public function testIterableSpyingWithArrayLikeObject()
    {
        $value = array('a' => 'b', 'c' => 'd');

        $stub = x\stub();
        $stub->setUseIterableSpies(true);
        $stub->returns(new ArrayObject($value));
        $result = $stub();

        $this->assertSame($value, iterator_to_array($result));
        $this->assertSame($value, iterator_to_array($result));

        $stub->iterated()->produced();
        $stub->iterated()->produced('b');
        $stub->iterated()->produced('d');
        $stub->iterated()->produced('a', 'b');
        $stub->iterated()->produced('c', 'd');

        $this->assertSame('b', $result['a']);
        $this->assertSame(2, count($result));
    }

    public function testDefaultStubAnswerCanBeOverridden()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->with('a', 'b')->returns(123);
        $mock = $handle->mock();

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));
        $this->assertSame('ef', $mock->testClassAMethodB('e', 'f'));
    }

    public function testFullMockDefaultStubAnswerCanBeOverridden()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
        $mock = $handle->mock();
        $handle->testClassAMethodA->with('a', 'b')->returns(123);

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertNull($mock->testClassAMethodA('c', 'd'));
        $this->assertNull($mock->testClassAMethodB('e', 'f'));
    }

    public function testMagicMockDefaultStubAnswerCanBeOverridden()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassB');
        $mock = $handle->mock();
        $handle->nonexistentA->with('a', 'b')->returns(123);

        $this->assertSame(123, $mock->nonexistentA('a', 'b'));
        $this->assertNull($mock->nonexistentA('c', 'd'));
        $this->assertNull($mock->nonexistentB('e', 'f'));
    }

    public function testDoesntCallParentOnInterfaceOnlyMock()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestInterfaceA');
        $mock = $handle->mock();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));
    }

    public function testDefaultArgumentsNotRecorded()
    {
        if (!$this->featureDetector->isSupported('parameter.type.self.override')) {
            $this->markTestSkipped('Requires support for overriding self parameters.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestClassC');
        $handle->mock()->methodB('a');

        $handle->methodB->calledWith('a');
    }

    public function testHandleStubOverriding()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->returns('x');
        $handle->testClassAMethodA->returns('y', 'z');

        $this->assertSame('y', $handle->mock()->testClassAMethodA());
        $this->assertSame('z', $handle->mock()->testClassAMethodA());
        $this->assertSame('z', $handle->mock()->testClassAMethodA());
    }

    public function testCanCallMockedInterfaceMethod()
    {
        $handle = x\partialMock(array('stdClass', 'Eloquent\Phony\Test\TestInterfaceA'));

        $this->assertNull($handle->mock()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedInterfaceMethodWithoutParentClass()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestInterfaceA');

        $this->assertNull($handle->mock()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('stdClass', 'Eloquent\Phony\Test\TestTraitA'));

        $this->assertSame('ab', $handle->mock()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithoutParentClass()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('Eloquent\Phony\Test\TestTraitA'));

        $this->assertSame('ab', $handle->mock()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('stdClass', 'Eloquent\Phony\Test\TestTraitC'));

        $this->assertNull($handle->mock()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethodWithoutParentClass()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('Eloquent\Phony\Test\TestTraitC'));

        $this->assertNull($handle->mock()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithInterface()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('Eloquent\Phony\Test\TestTraitH', 'Eloquent\Phony\Test\TestInterfaceE'));

        $this->assertSame('a', $handle->mock()->methodA());
    }

    public function testCanMockClassWithPrivateConstructor()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassD');

        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassD', $handle->mock());
    }

    public function testCanMockTraitWithPrivateConstructor()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitF', array('a', 'b'));

        $this->assertSame(array('a', 'b'), $handle->mock()->constructorArguments);
    }

    public function testCanMockClassAndCallPrivateConstructor()
    {
        if (!$this->featureDetector->isSupported('closure.bind')) {
            $this->markTestSkipped('Requires closure binding.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestClassD', array('a', 'b'));

        $this->assertSame(array('a', 'b'), $handle->mock()->constructorArguments);
    }

    public function testMatcherAdaptationForBooleanValues()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->with(true)->returns('a');

        $this->assertNull($handle->mock()->testClassAMethodA());
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
            array(
                array(
                    'file' => __FILE__,
                    'line' => $line,
                    'function' => 'called',
                    'class' => 'Eloquent\Phony\Spy\SpyVerifier',
                    'type' => '->',
                    'args' => array(),
                ),
            ),
            $exception->getTrace()
        );
    }

    public function testAssertionExceptionTrimmingWithEmptyTrace()
    {
        $exception = new Exception();
        $reflector = new ReflectionClass('Exception');
        $traceProperty = $reflector->getProperty('trace');
        $traceProperty->setAccessible(true);
        $traceProperty->setValue($exception, array());
        AssertionException::trim($exception);

        $this->assertNull($exception->getFile());
        $this->assertNull($exception->getLine());
        $this->assertSame(array(), $exception->getTrace());
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
        $this->assertInstanceOf('PDOStatement', x\mock('PDOStatement')->mock());
    }

    public function testTraitConstructorCalling()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitD', array('a', 'b', 'c'));

        $this->assertSame(array('a', 'b', 'c'), $handle->mock()->constructorArguments);
    }

    public function testTraitConstructorConflictResolution()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(
            array('Eloquent\Phony\Test\TestTraitD', 'Eloquent\Phony\Test\TestTraitE'),
            array('a', 'b', 'c')
        );

        $this->assertSame(array('a', 'b', 'c'), $handle->mock()->constructorArguments);
    }

    public function testCallAtWithAssertionResult()
    {
        $spy = x\spy();
        $spy('a', 1);
        $spy('b', 1);
        $spy('a', 2);

        $this->assertSame(array('a', 2), $spy->calledWith('a', '*')->callAt(1)->arguments()->all());
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

        $this->assertSame($handle->mock(), $handle->mock()->testClassAMethodA());
        $this->assertSame(array($handle->mock()), $callArguments);
    }

    public function testOrderVerification()
    {
        $spy = x\spy();
        $spy('a');
        $spy('b');
        $spy('c');
        $spy('d');

        x\inOrder(
            $spy->calledWith('a'),
            x\anyOrder(
                $spy->calledWith('c'),
                $spy->calledWith('b')
            ),
            $spy->calledWith('d')
        );
    }

    public function testCanForwardAfterFullMock()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
        $mock = $handle->mock();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));

        $handle->testClassAMethodA->returns('x');

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));

        $handle->testClassAMethodA->forwards();

        $this->assertSame('ab', $mock->testClassAMethodA('a', 'b'));
    }

    public function testCanForwardToMagicCallAfterFullMock()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassB');
        $mock = $handle->mock();

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
        $mock = $handle->mock();

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

        $this->assertInstanceOf('Exception', $handle->mock());
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
        $mock = x\partialMock(array('methodA' => new TestInvocable()))->mock();

        $this->assertSame(array('invokeWith', array('a', 'b')), $mock->methodA('a', 'b'));
    }

    public function testMockWithUncallableMagicMethod()
    {
        $mock = x\mock('Eloquent\Phony\Test\TestInterfaceD')->mock();

        $this->assertNull($mock->nonexistent());
    }

    public function testNoInteraction()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceD');

        $handle->noInteraction();
    }

    public function testCallsArgumentWithFullMockImplicitReturns()
    {
        $handle = Phony::mock('Eloquent\Phony\Test\TestClassA');
        $handle->testClassAMethodA->callsArgument(0);
        $spy = Phony::spy();

        $this->assertNull($handle->mock()->testClassAMethodA($spy));
        $spy->called();
    }

    public function testIncompleteCalls()
    {
        $test = $this;
        $context = (object) array('spy' => null);
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
        $stub->returns(array(), array());
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
        if (!$this->featureDetector->isSupported('class.anonymous')) {
            $this->markTestSkipped('Requires anonymous classes.');
        }

        $instance = eval('return new class {};');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\AnonymousClassException');
        x\mock(get_class($instance));
    }

    public function testPartialMockOfMagicCallTrait()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitJ');
        $mock = $handle->mock();

        $this->assertSame('magic a bc', $mock->a('b', 'c'));
        $handle->a->calledWith('b', 'c');
    }

    public function testPartialMockOfStaticMagicCallTrait()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $mock = x\partialMock('Eloquent\Phony\Test\TestTraitJ')->mock();
        $class = get_class($mock);

        $this->assertSame('magic a bc', $class::a('b', 'c'));
        x\onStatic($mock)->a->calledWith('b', 'c');
    }

    public function testInvalidStubUsageWithInvoke()
    {
        $stub = x\stub()->with();

        $this->setExpectedException('Eloquent\Phony\Stub\Exception\UnusedStubCriteriaException');
        $stub();
    }

    public function testInvalidStubUsageWithDestructor()
    {
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('Destructor is unpredictable under HHVM.');
        }

        $this->expectOutputString(
            "WARNING: Stub criteria '<none>' were never used. Check for incomplete stub rules." . PHP_EOL
        );

        call_user_func(
            function () {
                x\stub()->with();
            }
        );
        gc_collect_cycles();
    }

    public function testAutomaticInstanceHandleAdaptation()
    {
        $handleA = x\mock();
        $mockA = $handleA->mock();
        $handleB = x\mock(array('methodA' => function () {}));
        $mockB = $handleB->mock();
        $handleB->methodA->returns($handleA);
        $handleB->methodA->with($handleA)->returns('a');

        $this->assertSame($handleA->mock(), $mockB->methodA());
        $this->assertSame('a', $mockB->methodA($handleA->mock()));
        $handleB->methodA->calledWith($handleA);
        $handleB->methodA->returned($handleA);
    }

    public function testReturnByReferenceMocking()
    {
        $a = 'a';
        $b = 'b';
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassG');
        $mock = $handle->mock();
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
        $foo = x\partialMock(array('test' => function () { return 'foo'; }))->mock();
        $bar = x\partialMock(array('test' => function () { return 'bar'; }))->mock();

        $this->assertSame('foo', $foo->test());
        $this->assertSame('bar', $bar->test());
    }

    public function testAdHocMocksWithMagicSelf()
    {
        $mock = x\partialMock(array('test' => function ($phonySelf) { return $phonySelf; }))->mock();

        $this->assertSame($mock, $mock->test());
    }

    public function testAdHocMocksWithMagicSelfOutput()
    {
        $builder = x\mockBuilder(array('test' => function ($phonySelf) { return $phonySelf; }))
            ->named('PhonyTestAdHocMocksWithMagicSelfOutput');
        $mock = $builder->get();
        $handle = x\on($mock)->setLabel('label');

        $this->setExpectedException(
            'PHPUnit_Framework_AssertionFailedError',
            'PhonyTestAdHocMocksWithMagicSelfOutput[label]->test'
        );
        $handle->test->calledWith('a');
    }

    public function testBasicGeneratorStubbing()
    {
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }

        $stub = x\stub()
            ->generates(array('a' => 'b', 'c'))
                ->yields('d', 'e')
                ->yields('f')
                ->yields()
                ->returns();

        $generator = $stub();
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame(array('a' => 'b', 0 => 'c', 'd' => 'e', 1 => 'f', 2 => null), $actual);
    }

    public function testGeneratorStubbingWithReturnValue()
    {
        if (!$this->featureDetector->isSupported('generator.return')) {
            $this->markTestSkipped('Requires generator return values.');
        }

        $stub = x\stub()->generates()->returns('d');

        $generator = $stub();
        iterator_to_array($generator);

        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame('d', $generator->getReturn());
    }

    public function testGeneratorStubbingWithMultipleAnswers()
    {
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }

        $stub = x\stub()
            ->generates()->yields('a')->returns()
            ->returns('b')
            ->generates()->yields('c')->returns();

        $this->assertSame(array('a'), iterator_to_array($stub()));
        $this->assertSame('b', $stub());
        $this->assertSame(array('c'), iterator_to_array($stub()));
    }

    public function testGeneratorStubbingWithEmptyGenerator()
    {
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }

        $stub = x\stub();
        $stub->generates();

        $generator = $stub();
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame(array(), $actual);
    }

    public function testAssertionExceptionConstruction()
    {
        $actual = new AssertionException('You done goofed.');

        $this->assertNotNull($actual);
    }

    public function testCalledOnWithSpies()
    {
        if (!$this->featureDetector->isSupported('closure.bind')) {
            $this->markTestSkipped('Requires closure binding.');
        }

        $closure = function () {};
        $object = (object) array();
        $closure = $closure->bindTo($object);
        $spy = x\spy($closure);
        $spy();

        $spy->calledOn($object);
    }

    public function testCalledOnWithStubs()
    {
        if (!$this->featureDetector->isSupported('closure.bind')) {
            $this->markTestSkipped('Requires closure binding.');
        }

        $closure = function () {};
        $object = (object) array();
        $closure = $closure->bindTo($object);
        $stub = x\stub($closure);
        $stub();

        $stub->calledOn($object);
    }

    public function testCalledOnWithMocks()
    {
        if (!$this->featureDetector->isSupported('closure.bind')) {
            $this->markTestSkipped('Requires closure binding.');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestClassA');
        $mock = $handle->mock();
        $mock->testClassAMethodA();

        $handle->testClassAMethodA->calledOn($mock);
    }

    public function testCalledOnWithCustomMethods()
    {
        if (!$this->featureDetector->isSupported('closure.bind')) {
            $this->markTestSkipped('Requires closure binding.');
        }

        $handle = x\mock(array('a' => function () {}));
        $mock = $handle->mock();
        $mock->a();

        $handle->a->calledOn($mock);
    }
}
