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
    public function testSpy()
    {
        $spy = Phony::spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $spy->assertCalledWith('a', 'b', 'c');
        $spy->assertCalledWith('a', 'b');
        $spy->assertCalledWith('a');
        $spy->assertCalledWith();
        $spy->assertCalledWith(111);
        $spy->assertCalledWith($this->identicalTo('a'), $this->anything());
        $spy->callAt(0)->assertCalledWith('a', 'b', 'c');
        $spy->callAt(1)->assertCalledWith(111);
    }

    public function testSpyFunction()
    {
        $spy = a\spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $spy->assertCalledWith('a', 'b', 'c');
        $spy->assertCalledWith('a', 'b');
        $spy->assertCalledWith('a');
        $spy->assertCalledWith();
        $spy->assertCalledWith(111);
        $spy->assertCalledWith($this->identicalTo('a'), $this->anything());
        $spy->callAt(0)->assertCalledWith('a', 'b', 'c');
        $spy->callAt(1)->assertCalledWith(111);
    }

    public function testStub()
    {
        $stub = Phony::stub()
            ->returns('x')
            ->with(111)->returns('y');

        $this->assertSame('x', $stub('a', 'b', 'c'));
        $this->assertSame('y', $stub(111));
        $stub->assertCalledWith('a', 'b', 'c');
        $stub->assertCalledWith('a', 'b');
        $stub->assertCalledWith('a');
        $stub->assertCalledWith();
        $stub->assertCalledWith(111);
        $stub->assertCalledWith($this->identicalTo('a'), $this->anything());
        $stub->callAt(0)->assertCalledWith('a', 'b', 'c');
        $stub->callAt(1)->assertCalledWith(111);
        $stub->assertReturned('x');
        $stub->assertReturned('y');
    }

    public function testStubFunction()
    {
        $stub = a\stub()
            ->returns('x')
            ->with(111)->returns('y');

        $this->assertSame('x', $stub('a', 'b', 'c'));
        $this->assertSame('y', $stub(111));
        $stub->assertCalledWith('a', 'b', 'c');
        $stub->assertCalledWith('a', 'b');
        $stub->assertCalledWith('a');
        $stub->assertCalledWith();
        $stub->assertCalledWith(111);
        $stub->assertCalledWith($this->identicalTo('a'), $this->anything());
        $stub->callAt(0)->assertCalledWith('a', 'b', 'c');
        $stub->callAt(1)->assertCalledWith(111);
        $stub->assertReturned('x');
        $stub->assertReturned('y');
    }
}
