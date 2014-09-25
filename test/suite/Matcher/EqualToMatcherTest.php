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

use Eloquent\Phony\Comparator\DeepComparator;
use PHPUnit_Framework_TestCase;

class EqualToMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->value = 'value';
        $this->comparator = new DeepComparator();
        $this->subject = new EqualToMatcher($this->value, $this->comparator);
    }

    public function testConstructor()
    {
        $this->assertSame($this->value, $this->subject->value());
        $this->assertSame($this->comparator, $this->subject->comparator());
        $this->assertSame("'value'", $this->subject->describe());
        $this->assertSame("'value'", strval($this->subject));
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EqualToMatcher($this->value);

        $this->assertSame(DeepComparator::instance(), $this->subject->comparator());
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches($this->value));
        $this->assertFalse($this->subject->matches('anotherValue'));
    }
}
