<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition\Method;

use PHPUnit_Framework_TestCase;
use ReflectionFunction;

class CustomMethodDefinitionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->isStatic = false;
        $this->name = 'name';
        $this->callback = function () {};
        $this->subject = new CustomMethodDefinition($this->isStatic, $this->name, $this->callback);
    }

    public function testConstructor()
    {
        $this->assertSame($this->isStatic, $this->subject->isStatic());
        $this->assertTrue($this->subject->isCustom());
        $this->assertSame('public', $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertEquals(new ReflectionFunction($this->callback), $this->subject->method());
        $this->assertSame($this->callback, $this->subject->callback());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CustomMethodDefinition($this->isStatic, $this->name);

        $this->assertInternalType('object', $this->subject->callback());
        $this->assertTrue(is_callable($this->subject->callback()));
        $this->assertNull(call_user_func($this->subject->callback()));
    }
}
