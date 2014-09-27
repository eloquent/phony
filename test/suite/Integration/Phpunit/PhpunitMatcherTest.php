<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use PHPUnit_Framework_TestCase;

class PhpunitMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcher = $this->equalTo('value');
        $this->subject = new PhpunitMatcher($this->matcher);

        $this->description = 'is equal to <string:value>';
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcher, $this->subject->matcher());
        $this->assertSame($this->description, $this->subject->describe());
        $this->assertSame($this->description, strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('value'));
        $this->assertFalse($this->subject->matches('anotherValue'));
    }
}