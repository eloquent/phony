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
use PHPUnit_Framework_TestCase;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testSpy()
    {
        $subject = function () {};
        $expected = new SpyVerifier(new Spy($subject));
        $actual = Phony::spy($subject);

        $this->assertEquals($expected, $actual);
        $this->assertSame($subject, $actual->subject());
    }
}
