<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
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
        $this->method = new ReflectionFunction($this->callback);
        $this->subject = new CustomMethodDefinition($this->isStatic, $this->name, $this->method, $this->callback);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->subject->isCallable());
        $this->assertSame($this->isStatic, $this->subject->isStatic());
        $this->assertTrue($this->subject->isCustom());
        $this->assertSame('public', $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame($this->callback, $this->subject->callback());
    }
}
