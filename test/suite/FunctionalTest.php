<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Phpunit as x;
use Eloquent\Phony\Phpunit\Phony;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
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

    public function testTraversableSpyingStatic()
    {
        $stub = Phony::stub(null, null, true);
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
        $stub = x\stub(null, null, true);
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
}
