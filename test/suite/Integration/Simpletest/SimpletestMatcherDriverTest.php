<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use EqualExpectation;
use PHPUnit_Framework_TestCase;

class SimpletestMatcherDriverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new SimpletestMatcherDriver;
    }

    public function testAdapt()
    {
        $object = (object) array();
        $matcher = new EqualExpectation('value');
        $expected = new SimpletestMatcher($matcher);

        $this->assertTrue($this->subject->adapt($matcher));
        $this->assertEquals($expected, $matcher);
        $this->assertFalse($this->subject->adapt($object));
    }
}
