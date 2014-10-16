<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition;

use PHPUnit_Framework_TestCase;

class CustomMethodDefinitionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->isStatic = false;
        $this->name = 'name';
        $this->closure = function () {};
        $this->subject = new CustomMethodDefinition($this->isStatic, $this->name, $this->closure);
    }

    public function testConstructor()
    {
        $this->assertSame($this->isStatic, $this->subject->isStatic());
        $this->assertTrue($this->subject->isCustom());
        $this->assertSame(RealMethodDefinition::ACCESS_LEVEL_PUBLIC, $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertNull($this->subject->method());
        $this->assertSame($this->closure, $this->subject->closure());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CustomMethodDefinition($this->isStatic, $this->name);

        $this->assertInstanceOf('Closure', $this->subject->closure());
        $this->assertNull(call_user_func($this->subject->closure()));
    }
}
