<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Phpunit as a;
use Eloquent\Phony\Phpunit\Phony;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    public function testSpyStatic()
    {
        $spy = Phony::spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $spy->twice()->called();
        $spy->calledWith('a', 'b', 'c');
        $spy->calledWith('a', 'b', Phony::wildcard());
        $spy->calledWith('a', Phony::wildcard());
        $spy->calledWith(Phony::wildcard());
        $spy->calledWith(111);
        $spy->calledWith($this->identicalTo('a'), Phony::wildcard($this->anything()));
        $spy->callAt(0)->calledWith('a', 'b', 'c');
        $spy->callAt(1)->calledWith(111);
    }

    public function testSpyFunction()
    {
        $spy = a\spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $spy->twice()->called();
        $spy->calledWith('a', 'b', 'c');
        $spy->calledWith('a', 'b', a\wildcard());
        $spy->calledWith('a', a\wildcard());
        $spy->calledWith(a\wildcard());
        $spy->calledWith(111);
        $spy->calledWith($this->identicalTo('a'), a\wildcard($this->anything()));
        $spy->callAt(0)->calledWith('a', 'b', 'c');
        $spy->callAt(1)->calledWith(111);
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
        $stub->calledWith('a', 'b', Phony::wildcard());
        $stub->calledWith('a', Phony::wildcard());
        $stub->calledWith(Phony::wildcard());
        $stub->calledWith(111);
        $stub->calledWith($this->identicalTo('a'), Phony::wildcard($this->anything()));
        $stub->callAt(0)->calledWith('a', 'b', 'c');
        $stub->callAt(1)->calledWith(111);
        $stub->returned('x');
        $stub->returned('y');
    }

    public function testStubFunction()
    {
        $stub = a\stub()
            ->returns('x')
            ->with(111)->returns('y');

        $this->assertSame('x', $stub('a', 'b', 'c'));
        $this->assertSame('y', $stub(111));
        $stub->twice()->called();
        $stub->calledWith('a', 'b', 'c');
        $stub->calledWith('a', 'b', a\wildcard());
        $stub->calledWith('a', a\wildcard());
        $stub->calledWith(a\wildcard());
        $stub->calledWith(111);
        $stub->calledWith($this->identicalTo('a'), a\wildcard($this->anything()));
        $stub->callAt(0)->calledWith('a', 'b', 'c');
        $stub->callAt(1)->calledWith(111);
        $stub->returned('x');
        $stub->returned('y');
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
        $stub = a\stub(null, null, true);
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
}
