<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony;

use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Stub\Stub;
use Eloquent\Phony\Stub\StubVerifier;
use PHPUnit_Framework_TestCase;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testSpy()
    {
        $callback = function () {};
        $expected = new SpyVerifier(new Spy($callback));
        $actual = Phony::spy($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->callback());
    }

    public function testStub()
    {
        $callback = function () {};
        $expected = new StubVerifier(new Stub($callback));
        $actual = Phony::stub($callback);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->stub()->callback());
        $this->assertSame($actual->stub(), $actual->spy()->callback());
    }
}
