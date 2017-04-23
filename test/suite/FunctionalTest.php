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

class FunctionalTest extends PHPUnit_Framework_TestCase
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
            array('a', 'b'),
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
            array('a', 'b'),
            $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->arguments()->all()
        );
        $this->assertSame('b', $handle->testClassAMethodA->calledWith('a', '*')->firstCall()->argument(1));
    }

    public function testMockCalls()
    {
        $mock = x\partialMock('Eloquent\Phony\Test\TestClassB', array('A', 'B'))->get();
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

        $this->assertSame(array(1, 2), $handle->get()->method(1, 2));
    }

    public function testVariadicParameterMockingWithType()
    {
        if (!$this->featureDetector->isSupported('parameter.variadic')) {
            $this->markTestSkipped('Requires variadic parameters.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('Broken because of https://github.com/facebook/hhvm/issues/5762');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithVariadicParameterWithType');
        $handle->method->does(
            function () {
                return func_get_args();
            }
        );
        $a = (object) array();
        $b = (object) array();

        $this->assertSame(array($a, $b), $handle->get()->method($a, $b));
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
        if (!$this->featureDetector->isSupported('parameter.hint.scalar')) {
            $this->markTestSkipped('Requires scalar type hints.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM scalar type hints are bugged.');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint');

        $handle->get()->method(123, 1.23, '<string>', true);
        $handle->method->calledWith(123, 1.23, '<string>', true);
    }

    public function testReturnTypeMocking()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM scalar type hints are bugged.');
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

        $this->assertSame($object, $handle->get()->classType('x'));
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassA', $handle->get()->classType());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Mock', $handle->get()->classType());
        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\InstanceHandle', x\on($handle->get()->classType()));
        $this->assertSame(123, $handle->get()->scalarType('x'));
        $this->assertSame(0, $handle->get()->scalarType());
    }

    public function testMagicMethodReturnTypeMocking()
    {
        if (!$this->featureDetector->isSupported('return.type')) {
            $this->markTestSkipped('Requires return type declarations.');
        }
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM scalar type hints are bugged.');
        }

        $mock = x\mock('Eloquent\Phony\Test\TestInterfaceWithReturnType')->get();

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
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM scalar type hints are bugged.');
        }

        $handle = x\mock('Eloquent\Phony\Test\TestInterfaceWithReturnType');
        $handle->scalarType->returns('<string>');

        $this->setExpectedException('TypeError');
        $handle->get()->scalarType();
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
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM scalar type hints are bugged.');
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
        if ($this->featureDetector->isSupported('runtime.hhvm')) {
            $this->markTestSkipped('HHVM scalar type hints are bugged.');
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
        if (!$this->featureDetector->isSupported('parameter.type.self.override')) {
            $this->markTestSkipped('Requires support for overriding self parameters.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestClassC');
        $handle->get()->methodB('a');

        $handle->methodB->calledWith('a');
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
        $handle = x\partialMock(array('stdClass', 'Eloquent\Phony\Test\TestInterfaceA'));

        $this->assertNull($handle->get()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedInterfaceMethodWithoutParentClass()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestInterfaceA');

        $this->assertNull($handle->get()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('stdClass', 'Eloquent\Phony\Test\TestTraitA'));

        $this->assertSame('ab', $handle->get()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithoutParentClass()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('Eloquent\Phony\Test\TestTraitA'));

        $this->assertSame('ab', $handle->get()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('stdClass', 'Eloquent\Phony\Test\TestTraitC'));

        $this->assertNull($handle->get()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethodWithoutParentClass()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('Eloquent\Phony\Test\TestTraitC'));

        $this->assertNull($handle->get()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithInterface()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock(array('Eloquent\Phony\Test\TestTraitH', 'Eloquent\Phony\Test\TestInterfaceE'));

        $this->assertSame('a', $handle->get()->methodA());
    }

    public function testCanMockClassWithPrivateConstructor()
    {
        $handle = x\partialMock('Eloquent\Phony\Test\TestClassD');

        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassD', $handle->get());
    }

    public function testCanMockTraitWithPrivateConstructor()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitF', array('a', 'b'));

        $this->assertSame(array('a', 'b'), $handle->get()->constructorArguments);
    }

    public function testCanMockClassAndCallPrivateConstructor()
    {
        if (!$this->featureDetector->isSupported('closure.bind')) {
            $this->markTestSkipped('Requires closure binding.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestClassD', array('a', 'b'));

        $this->assertSame(array('a', 'b'), $handle->get()->constructorArguments);
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
        $this->assertInstanceOf('PDOStatement', x\mock('PDOStatement')->get());
    }

    public function testTraitConstructorCalling()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $handle = x\partialMock('Eloquent\Phony\Test\TestTraitD', array('a', 'b', 'c'));

        $this->assertSame(array('a', 'b', 'c'), $handle->get()->constructorArguments);
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

        $this->assertSame(array('a', 'b', 'c'), $handle->get()->constructorArguments);
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

        $this->assertSame($handle->get(), $handle->get()->testClassAMethodA());
        $this->assertSame(array($handle->get()), $callArguments);
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
        $mock = x\partialMock(array('methodA' => new TestInvocable()))->get();

        $this->assertSame(array('invokeWith', array('a', 'b')), $mock->methodA('a', 'b'));
    }

    public function testMockWithUncallableMagicMethod()
    {
        $mock = x\mock('Eloquent\Phony\Test\TestInterfaceD')->get();

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

        $this->assertNull($handle->get()->testClassAMethodA($spy));
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
        $mock = $handle->get();

        $this->assertSame('magic a bc', $mock->a('b', 'c'));
        $handle->a->calledWith('b', 'c');
    }

    public function testPartialMockOfStaticMagicCallTrait()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $mock = x\partialMock('Eloquent\Phony\Test\TestTraitJ')->get();
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

    public function testMockHandleSubstitution()
    {
        $handleA = x\mock();
        $handleA->get();
        $handleB = x\mock(array('methodA' => function () {}));
        $mockB = $handleB->get();
        $handleB->methodA->returns($handleA);
        $handleB->methodA->with($handleA)->returns('a');

        $this->assertSame($handleA->get(), $mockB->methodA());
        $this->assertSame('a', $mockB->methodA($handleA->get()));
        $handleB->methodA->calledWith($handleA);
        $handleB->methodA->returned($handleA);
    }

    public function testIterableSpySubstitution()
    {
        $stub = x\stub()->setUseIterableSpies(true)->returnsArgument();
        $iterable = array('a', 'b');
        $iterableSpy = $stub($iterable);
        $spy = x\spy();
        $spy($iterable);
        $spy($iterableSpy);

        $stub->returned($iterable);
        $stub->returned($iterableSpy);
        $stub->returned(x\equalTo($iterable));
        $stub->never()->returned(x\equalTo($iterableSpy));
        $spy->callAt(0)->calledWith($iterable);
        $spy->callAt(0)->calledWith($iterableSpy);
        $spy->callAt(0)->never()->calledWith(x\equalTo($iterableSpy));
        $spy->callAt(1)->calledWith($iterable);
        $spy->callAt(1)->calledWith($iterableSpy);
        $spy->callAt(1)->never()->calledWith(x\equalTo($iterable));
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
        $foo = x\partialMock(array('test' => function () { return 'foo'; }))->get();
        $bar = x\partialMock(array('test' => function () { return 'bar'; }))->get();

        $this->assertSame('foo', $foo->test());
        $this->assertSame('bar', $bar->test());
    }

    public function testAdHocMocksWithMagicSelf()
    {
        $mock = x\partialMock(array('test' => function ($phonySelf) { return $phonySelf; }))->get();

        $this->assertSame($mock, $mock->test());
    }

    public function testAdHocMocksWithMagicSelfOutput()
    {
        $builder = x\mockBuilder(array('test' => function ($phonySelf) { return $phonySelf; }))
            ->named('PhonyTestAdHocMocksWithMagicSelfOutput');
        $mock = $builder->get();
        $handle = x\on($mock)->setLabel('label');

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
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

    public function testFinalConstructorBypass()
    {
        $handle = x\mock('Eloquent\Phony\Test\TestClassI');
        $mock = $handle->get();

        $this->assertNull($mock->constructorArguments);
    }

    public function testIterableSpyDoubleWrappingWithArray()
    {
        $stub = x\stub()->setUseIterableSpies(true)->returns(array('a', 'b'));
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
        $this->assertSame(array('a', 'b'), $iterableSpyBContents);
        $singleWrapped->twice()->produced('a');
        $singleWrapped->once()->produced('b');
        $doubleWrapped->once()->produced('a');
        $doubleWrapped->once()->produced('b');
    }

    public function testIterableSpyDoubleWrappingWithTraversable()
    {
        $stub = x\stub()->setUseIterableSpies(true)->returns(new ArrayIterator(array('a', 'b')));
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
        $this->assertSame(array('a', 'b'), $iterableSpyBContents);
        $singleWrapped->twice()->produced('a');
        $singleWrapped->once()->produced('b');
        $doubleWrapped->once()->produced('a');
        $doubleWrapped->once()->produced('b');
    }

    public function testIterableSpyDoubleWrappingWithGenerator()
    {
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }
        if (!$this->featureDetector->isSupported('generator.implicit-next')) {
            $this->markTestSkipped('Requires implicit next() generators.');
        }

        $stub = x\stub()->generates()->yieldsFrom(array('a', 'b', 'c'))->returns();
        $generatorSpyA = $stub();
        $stub->returns($generatorSpyA);
        $generatorSpyB = $stub();

        $this->assertSame($generatorSpyA, $generatorSpyB->_phonySubject);

        $first = true;
        $generatorSpyAContents = array();
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

        $this->assertSame(array('a', 'b'), $generatorSpyAContents);
        $this->assertSame(array(1 => 'b', 2 => 'c'), $generatorSpyBContents);
        $singleWrapped->once()->produced(0, 'a');
        $singleWrapped->once()->produced(1, 'b');
        $singleWrapped->once()->produced(2, 'c');
        $doubleWrapped->never()->produced('a');
        $doubleWrapped->once()->produced(1, 'b');
        $doubleWrapped->once()->produced(2, 'c');
    }

    public function testIterableSpyDoubleWrappingWithGeneratorWithoutImplicitNext()
    {
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }
        if ($this->featureDetector->isSupported('generator.implicit-next')) {
            $this->markTestSkipped('Requires explicit next() generators.');
        }

        $stub = x\stub()->generates()->yieldsFrom(array('a', 'b', 'c'))->returns();
        $generatorSpyA = $stub();
        $stub->returns($generatorSpyA);
        $generatorSpyB = $stub();

        $this->assertSame($generatorSpyA, $generatorSpyB->_phonySubject);

        $first = true;
        $generatorSpyAContents = array();
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

        $this->assertSame(array('a', 'b'), $generatorSpyAContents);
        $this->assertSame(array(2 => 'c'), $generatorSpyBContents);
        $singleWrapped->once()->produced(0, 'a');
        $singleWrapped->once()->produced(1, 'b');
        $singleWrapped->once()->produced(2, 'c');
        $doubleWrapped->never()->produced('a');
        $doubleWrapped->never()->produced('b');
        $doubleWrapped->once()->produced(2, 'c');
    }

    public function exporterExamplesTest()
    {
        $sequence = array(1, 2);
        $repeatedSequences = array(&$sequence, &$sequence);
        $inner = (object) array('a' => 1);
        $repeatedObjects = (object) array('b' => $inner, 'c' => $inner);
        $identifierCollision = array((object) array(), array((object) array()));
        $inner = new ClassA();
        $inner->c = 'd';
        $classNameExclusion = (object) array('a' => $inner, 'b' => $inner);

        return array(
            // The exporter format
            'null'           => array(null,                               'null'),
            'true'           => array(true,                               'true'),
            'false'          => array(false,                              'false'),
            'integer'        => array(111,                                '111'),
            'float'          => array(1.11,                               '1.110000e+0'),
            'float string'   => array('1.11',                             '"1.11"'),
            'string'         => array("a\nb",                             '"a\nb"'),
            'resource'       => array(STDIN,                              'resource#1'),
            'sequence'       => array($sequence,                          '#0[1, 2]'),
            'map'            => array(array('a' => 1, 'b' => 2),          '#0["a": 1, "b": 2]'),
            'generic object' => array((object) array('a' => 1, 'b' => 2), '#0{a: 1, b: 2}'),
            'object'         => array(new ClassA(),                       'ClassA#0{}'),

            // Export identifiers and references
            'repeated sequence'       => array($repeatedSequences, '#0[#1[1, 2], &1[]]'),
            'repeated generic object' => array($repeatedObjects,   '#0{b: #1{a: 1}, c: &1{}}'),

            // Export reference types
            'identifier collision' => array($identifierCollision, '#0[#0{}, #1[#1{}]]'),

            // Export reference exclusions
            'class name exclusion' => array($classNameExclusion, '#0{a: ClassA#1{c: "d"}, b: &1{}}'),

            // Exporting closures
            'closure' => array(function () {}, 'Closure#0{}[FunctionalTest.php:' . __LINE__ . ']'),

            // Exporting exceptions
            'exception'           => array(new Exception('a', 1, new Exception()), 'Exception#0{message: "a", code: 1, previous: Exception#1{}}'),
            'exception defaulted' => array(new RuntimeException(),                 'RuntimeException#0{}'),
        );
    }

    /**
     * @dataProvider exporterExamplesTest
     */
    public function testExporterExamples($value, $expected)
    {
        $this->exporter->reset();

        $this->assertSame($expected, $this->exporter->export($value, -1));
    }

    public function testExporterExamplesRepeatedWrappers()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $inner = x\mock('ClassA')->setLabel('mock-label');
        $value = array($inner, $inner);
        $this->exporter->reset();

        $this->assertSame(
            '#0[handle#0(PhonyMock_ClassA_0#1{}[mock-label]), &0()]',
            $this->exporter->export($value, -1)
        );
    }

    public function testExporterExamplesReferenceTypes()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $array = array();
        $object = (object) array();
        $wrapper = x\spy('implode')->setLabel('spy-label');
        $valueA = array(&$array, &$array);
        $valueB = array($object, $object);
        $valueC = array($wrapper, $wrapper);
        $this->exporter->reset();

        $this->assertSame('#0[#1[], &1[]]', $this->exporter->export($valueA, -1));
        $this->assertSame('#0[#0{}, &0{}]', $this->exporter->export($valueB, -1));
        $this->assertSame('#0[spy#1(implode)[spy-label], &1()]', $this->exporter->export($valueC, -1));
    }

    public function testExporterExamplesExcludeWrapperValue()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $inner = x\mock();
        $value = array($inner, $inner);
        $this->exporter->reset();

        $this->assertSame('#0[handle#0(PhonyMock_0#1{}[0]), &0()]', $this->exporter->export($value, -1));
    }

    public function testExporterExamplesIdentifierPersistenceObjects()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $a = (object) array();
        $b = (object) array();
        $c = x\mock();
        $valueA = array($a, $b, $c, $a);
        $valueB = array($b, $a, $b, $c);
        $this->exporter->reset();

        $this->assertSame('#0[#0{}, #1{}, handle#2(PhonyMock_0#3{}[0]), &0{}]', $this->exporter->export($valueA, -1));
        $this->assertSame('#0[#1{}, #0{}, &1{}, handle#2(PhonyMock_0#3{}[0])]', $this->exporter->export($valueB, -1));
    }

    public function testExporterExamplesIdentifierPersistenceArrays()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $a = array();
        $b = array();
        $valueA = array(&$a, &$b, &$a);
        $valueB = array(&$b, &$a, &$b);
        $this->exporter->reset();

        $this->assertSame('#0[#1[], #2[], &1[]]', $this->exporter->export($valueA, -1));
        $this->assertSame('#0[#1[], #2[], &1[]]', $this->exporter->export($valueB, -1));
    }

    public function testExporterExamplesRecursiveValues()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $recursiveArray = array();
        $recursiveArray[] = &$recursiveArray;
        $recursiveObject = (object) array();
        $recursiveObject->a = $recursiveObject;
        $this->exporter->reset();

        $this->assertSame('#0[&0[]]', $this->exporter->export($recursiveArray, -1));
        $this->assertSame('#0{a: &0{}}', $this->exporter->export($recursiveObject, -1));
    }

    public function testExporterExamplesMocks()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $handle = x\mock('ClassA')->setLabel('mock-label');
        $mock = $handle->get();
        $this->exporter->reset();

        $this->assertSame('PhonyMock_ClassA_0#0{}[mock-label]', $this->exporter->export($mock, -1));
        $this->assertSame('handle#1(PhonyMock_ClassA_0#0{}[mock-label])', $this->exporter->export($handle, -1));
    }

    public function testExporterExamplesStaticHandle()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $handle = x\mock('ClassA')->setLabel('mock-label');
        $staticHandle = x\onStatic($handle);
        $this->exporter->reset();

        $this->assertSame('static-handle#0(PhonyMock_ClassA_0)', $this->exporter->export($staticHandle, -1));
    }

    public function testExporterExamplesStubs()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $stub = x\stub('implode')->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertSame('stub#0(implode)[stub-label]', $this->exporter->export($stub, -1));
    }

    public function testExporterExamplesAnonymousStubs()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $stub = x\stub()->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertSame('stub#0[stub-label]', $this->exporter->export($stub, -1));
    }

    public function testExporterExamplesMockStubs()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $handle = x\mock('ClassA')->setLabel('mock-label');
        $staticHandle = x\onStatic($handle);
        $stubA = $handle->methodA->setLabel('stub-label');
        $stubB = $staticHandle->staticMethodA->setLabel('stub-label');
        $this->exporter->reset();

        $this->assertSame('stub#0(ClassA[mock-label]->methodA)[stub-label]', $this->exporter->export($stubA, -1));
        $this->assertSame('stub#1(ClassA::staticMethodA)[stub-label]', $this->exporter->export($stubB, -1));
    }

    public function testExporterExamplesSpies()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $spy = x\spy('implode')->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertSame('spy#0(implode)[spy-label]', $this->exporter->export($spy, -1));
    }

    public function testExporterExamplesAnonymousSpies()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $spy = x\spy()->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertSame('spy#0[spy-label]', $this->exporter->export($spy, -1));
    }

    public function testExporterExamplesMethodSpies()
    {
        $this->markTestSkipped('Requires process isolation to actually work.');

        $object = new ClassA();
        $spyA = x\spy(array($object, 'methodA'))->setLabel('spy-label');
        $spyB = x\spy(array('ClassA', 'staticMethodA'))->setLabel('spy-label');
        $this->exporter->reset();

        $this->assertSame('spy#0(ClassA->methodA)[spy-label]', $this->exporter->export($spyA, -1));
        $this->assertSame('spy#1(ClassA::staticMethodA)[spy-label]', $this->exporter->export($spyB, -1));
    }

    public function testExporterExamplesExportDepth()
    {
        $valueA = array(array(), array('a', 'b', 'c'));
        $valueB = array((object) array(), (object) array('a', 'b', 'c'));
        $this->exporter->reset();

        $this->assertSame('#0[#1[], #2[~3]]', $this->exporter->export($valueA));
        $this->assertSame('#0[#0{}, #1{~3}]', $this->exporter->export($valueB));
    }
}
