<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use PHPUnit_Framework_TestCase;
use SebastianBergmann\Comparator\Factory;

class EqualToMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->value = 'value';
        $this->comparatorFactory = new Factory();
        $this->subject = new EqualToMatcher($this->value, $this->comparatorFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->value, $this->subject->value());
        $this->assertSame($this->comparatorFactory, $this->subject->comparatorFactory());
        $this->assertSame("'value'", $this->subject->describe());
        $this->assertSame("'value'", strval($this->subject));
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EqualToMatcher($this->value);

        $this->assertEquals($this->comparatorFactory, $this->subject->comparatorFactory());
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches($this->value));
        $this->assertFalse($this->subject->matches('anotherValue'));
    }
}
