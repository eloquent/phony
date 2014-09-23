<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phake;

use Eloquent\Phony\Matcher\WildcardMatcher;
use Phake;
use PHPUnit_Framework_TestCase;

class PhakeMatcherDriverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new PhakeMatcherDriver();
    }

    public function testAdapt()
    {
        $object = (object) array();
        $matcher = Phake::equalTo('value');
        $expected = new PhakeMatcher($matcher);

        $this->assertTrue($this->subject->adapt($matcher));
        $this->assertEquals($expected, $matcher);
        $this->assertFalse($this->subject->adapt($object));
    }

    public function testAdaptWildcard()
    {
        $matcher = Phake::anyParameters();

        $this->assertTrue($this->subject->adapt($matcher));
        $this->assertSame(WildcardMatcher::instance(), $matcher);
    }
}
