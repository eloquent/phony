<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Phpunit as x;
use Eloquent\Phony\Phpunit\Phony;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->featureDetector = FeatureDetector::instance();
    }

    public function testMockingStatic()
    {
        $proxy = Phony::mock('Eloquent\Phony\Test\TestClassA');
        $proxy->testClassAMethodA('a', 'b')->returns('x');
        $mock = $proxy->mock();

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));

        $this->assertSame(array('a', 'b'), $proxy->testClassAMethodA->calledWith('a', '*')->arguments());
        $this->assertSame('b', $proxy->testClassAMethodA->calledWith('a', '*')->argument(1));

        $proxy->reset()->full();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));
        $this->assertNull($mock->testClassAMethodA('c', 'd'));
    }

    public function testMockingFunctions()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassA');
        $proxy->testClassAMethodA('a', 'b')->returns('x');
        $mock = $proxy->mock();

        $this->assertSame('x', $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));
        $this->assertSame(array('a', 'b'), $proxy->testClassAMethodA->calledWith('a', '*')->arguments());
        $this->assertSame('b', $proxy->testClassAMethodA->calledWith('a', '*')->argument(1));

        $proxy->reset()->full();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));
        $this->assertNull($mock->testClassAMethodA('c', 'd'));
    }

    public function testMockCalls()
    {
        $mock = x\mock('Eloquent\Phony\Test\TestClassB', array('A', 'B'))->mock();
        $e = 'e';
        $n = 'n';
        $q = 'q';
        $r = 'r';

        $this->assertSame(array('A', 'B'), $mock->constructorArguments);
        $this->assertSame('ab', $mock::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('cde', $mock::testClassAStaticMethodB('c', 'd', $e));
        x\verifyStatic($mock)->testClassAStaticMethodB('c', 'd', 'e');
        $this->assertSame('third', $e);
        $this->assertSame('fg', $mock::testClassBStaticMethodA('f', 'g'));
        $this->assertSame('hi', $mock::testClassBStaticMethodB('h', 'i'));
        $this->assertSame('jk', $mock->testClassAMethodA('j', 'k'));
        $this->assertSame('lmn', $mock->testClassAMethodB('l', 'm', $n));
        x\verify($mock)->testClassAMethodB('l', 'm', 'n');
        $this->assertSame('third', $n);
        $this->assertSame('op', $mock->testClassBMethodA('o', 'p'));
        $this->assertSame('qr', $mock->testClassBMethodB($q, $r));
        x\verify($mock)->testClassBMethodB('q', 'r');
        $this->assertSame('first', $q);
        $this->assertSame('second', $r);
    }

    public function testMagicMethodMocking()
    {
        $mock = x\mock('Eloquent\Phony\Test\TestClassB')->mock();

        $this->assertSame('static magic nonexistent ab', $mock::nonexistent('a', 'b'));
        $this->assertSame('magic nonexistent ab', $mock->nonexistent('a', 'b'));

        x\onStatic($mock)->__callStatic->with('nonexistent', array('a', 'b'))->returns('w');
        x\onStatic($mock)->nonexistent('c', 'd')->returns('x');
        x\on($mock)->__call->with('nonexistent', array('a', 'b'))->returns('y');
        x\on($mock)->nonexistent('c', 'd')->returns('z');

        $this->assertSame('w', $mock::nonexistent('a', 'b'));
        $this->assertSame('x', $mock::nonexistent('c', 'd'));
        $this->assertSame('static magic nonexistent ef', $mock::nonexistent('e', 'f'));
        $this->assertSame('y', $mock->nonexistent('a', 'b'));
        $this->assertSame('z', $mock->nonexistent('c', 'd'));
        $this->assertSame('magic nonexistent ef', $mock->nonexistent('e', 'f'));
    }

    public function testMockMocking()
    {
        $mock = x\mock();
        $mockMock = x\mock($mock->className());

        $this->assertInstanceOf(get_class($mock->mock()), $mockMock->mock());
        $this->assertNotInstanceOf(get_class($mockMock->mock()), $mock->mock());
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

    public function testTraversableSpyingStatic()
    {
        $stub = Phony::stub(null, null, null, true);
        $stub->returns(array('a' => 'b', 'c' => 'd'));
        iterator_to_array($stub());

        $stub->produced();
        $stub->produced('b');
        $stub->produced('d');
        $stub->produced('a', 'b');
        $stub->produced('c', 'd');
        $stub->producedAll('b', 'd');
        $stub->producedAll(array('a', 'b'), array('c', 'd'));
    }

    public function testTraversableSpyingFunction()
    {
        $stub = x\stub(null, null, null, true);
        $stub->returns(array('a' => 'b', 'c' => 'd'));
        iterator_to_array($stub());

        $stub->produced();
        $stub->produced('b');
        $stub->produced('d');
        $stub->produced('a', 'b');
        $stub->produced('c', 'd');
        $stub->producedAll('b', 'd');
        $stub->producedAll(array('a', 'b'), array('c', 'd'));
    }

    public function testDefaultStubAnswerCanBeOverridden()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassA');
        $proxy->testClassAMethodA('a', 'b')->returns(123);
        $mock = $proxy->mock();

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $mock->testClassAMethodA('c', 'd'));
        $this->assertSame('ef', $mock->testClassAMethodB('e', 'f'));
    }

    public function testFullMockDefaultStubAnswerCanBeOverridden()
    {
        $proxy = x\fullMock('Eloquent\Phony\Test\TestClassA');
        $mock = $proxy->mock();
        $proxy->testClassAMethodA('a', 'b')->returns(123);

        $this->assertSame(123, $mock->testClassAMethodA('a', 'b'));
        $this->assertNull($mock->testClassAMethodA('c', 'd'));
        $this->assertNull($mock->testClassAMethodB('e', 'f'));
    }

    public function testMagicMockDefaultStubAnswerCanBeOverridden()
    {
        $proxy = x\fullMock('Eloquent\Phony\Test\TestClassB');
        $mock = $proxy->mock();
        $proxy->nonexistentA('a', 'b')->returns(123);

        $this->assertSame(123, $mock->nonexistentA('a', 'b'));
        $this->assertNull($mock->nonexistentA('c', 'd'));
        $this->assertNull($mock->nonexistentB('e', 'f'));
    }

    public function testCanChainVerificationProxyCalls()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassA');
        $mock = $proxy->mock();
        $mock->testClassAMethodA('a', 'b');
        $mock->testClassAMethodA('c', 'd');

        x\verify($mock)->testClassAMethodA('a', 'b')->testClassAMethodA('c', 'd');
    }

    public function testDoesntCallParentOnInterfaceOnlyMock()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestInterfaceA');
        $mock = $proxy->mock();

        $this->assertNull($mock->testClassAMethodA('a', 'b'));
    }

    public function testDefaultArgumentsNotRecorded()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassC');
        $proxy->mock()->methodB('a');

        $proxy->methodB->calledWith('a');
    }

    public function testProxyStubOverriding()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassA');
        $proxy->testClassAMethodA->returns('x');
        $proxy->testClassAMethodA->returns('y', 'z');

        $this->assertSame('y', $proxy->mock()->testClassAMethodA());
        $this->assertSame('z', $proxy->mock()->testClassAMethodA());
        $this->assertSame('z', $proxy->mock()->testClassAMethodA());
    }

    public function testCanCallMockedInterfaceMethod()
    {
        $proxy = x\mock(array('stdClass', 'Eloquent\Phony\Test\TestInterfaceA'));

        $this->assertNull($proxy->mock()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedInterfaceMethodWithoutParentClass()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestInterfaceA');

        $this->assertNull($proxy->mock()->testClassAMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock(array('stdClass', 'Eloquent\Phony\Test\TestTraitA'));

        $this->assertSame('ab', $proxy->mock()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithoutParentClass()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock(array('Eloquent\Phony\Test\TestTraitA'));

        $this->assertSame('ab', $proxy->mock()->testClassAMethodB('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock(array('stdClass', 'Eloquent\Phony\Test\TestTraitC'));

        $this->assertNull($proxy->mock()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedAbstractTraitMethodWithoutParentClass()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock(array('Eloquent\Phony\Test\TestTraitC'));

        $this->assertNull($proxy->mock()->testTraitCMethodA('a', 'b'));
    }

    public function testCanCallMockedTraitMethodWithInterface()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock(array('Eloquent\Phony\Test\TestTraitA', 'Eloquent\Phony\Test\TestInterfaceA'));

        $this->assertSame('ab', $proxy->mock()->testClassAMethodB('a', 'b'));
    }

    public function testCanMockClassWithPrivateConstructor()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassD');

        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassD', $proxy->mock());
    }

    public function testCanMockTraitWithPrivateConstructor()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock('Eloquent\Phony\Test\TestTraitF', array('a', 'b'));

        $this->assertSame(array('a', 'b'), $proxy->mock()->constructorArguments);
    }

    public function testCanMockClassAndCallPrivateConstructor()
    {
        if (!$this->featureDetector->isSupported('closure.bind')) {
            $this->markTestSkipped('Requires closure binding.');
        }

        $proxy = x\mock('Eloquent\Phony\Test\TestClassD', array('a', 'b'));

        $this->assertSame(array('a', 'b'), $proxy->mock()->constructorArguments);
    }

    public function testSpyAssertionFailureOutput()
    {
        $spy = x\spy();
        $spy->setLabel('example');
        $spy('a', 'b');
        $expected = <<<'EOD'
Expected call on {spy}[example] with arguments like:
    <'c'>, <'d'>
Calls:
    - 'a', 'b'
EOD;

        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $expected);
        $spy->calledWith('c', 'd');
    }

    public function testMockAssertionFailureOutput()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassA', null, null, 'PhonyMockAssertionFailure');
        $proxy->setLabel('example');
        $proxy->mock()->testClassAMethodA('a', 'b');
        $expected = <<<'EOD'
Expected call on PhonyMockAssertionFailure[example]->testClassAMethodA with arguments like:
    <'c'>, <'d'>
Calls:
    - 'a', 'b'
EOD;

        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', $expected);
        $proxy->testClassAMethodA->calledWith('c', 'd');
    }

    public function testMatcherAdaptationForBooleanValues()
    {
        $proxy = x\fullMock('Eloquent\Phony\Test\TestClassA');
        $proxy->testClassAMethodA->with(true)->returns('a');

        $this->assertNull($proxy->mock()->testClassAMethodA());
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

    public function testProxyCaseInsensitivity()
    {
        $proxy = x\mock('Eloquent\Phony\Test\TestClassA');

        $this->assertSame($proxy->testClassAMethodA, $proxy->testclassamethoda);
    }

    public function testTraversableInterfaceMocking()
    {
        x\mock('Eloquent\Phony\Test\TestInterfaceC');

        $this->assertTrue(true);
    }

    public function testTraitConstructorCalling()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock('Eloquent\Phony\Test\TestTraitD', array('a', 'b', 'c'));

        $this->assertSame(array('a', 'b', 'c'), $proxy->mock()->constructorArguments);
    }

    public function testTraitConstructorConflictResolution()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $proxy = x\mock(
            array('Eloquent\Phony\Test\TestTraitD', 'Eloquent\Phony\Test\TestTraitE'),
            array('a', 'b', 'c')
        );

        $this->assertSame(array('a', 'b', 'c'), $proxy->mock()->constructorArguments);
    }
}
