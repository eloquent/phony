<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition\Method;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class RealMethodDefinitionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWithPublicStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodA');
        $this->name = 'name';
        $this->subject = new RealMethodDefinition($this->method, $this->name);

        $this->assertTrue($this->subject->isCallable());
        $this->assertTrue($this->subject->isStatic());
        $this->assertFalse($this->subject->isCustom());
        $this->assertSame('public', $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertNull($this->subject->callback());
    }

    public function testConstructorWithPublicNonStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodA');
        $this->name = 'name';
        $this->subject = new RealMethodDefinition($this->method, $this->name);

        $this->assertTrue($this->subject->isCallable());
        $this->assertFalse($this->subject->isStatic());
        $this->assertFalse($this->subject->isCustom());
        $this->assertSame('public', $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertNull($this->subject->callback());
    }

    public function testConstructorWithProtectedStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodC');
        $this->name = 'name';
        $this->subject = new RealMethodDefinition($this->method, $this->name);

        $this->assertTrue($this->subject->isCallable());
        $this->assertTrue($this->subject->isStatic());
        $this->assertFalse($this->subject->isCustom());
        $this->assertSame('protected', $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertNull($this->subject->callback());
    }

    public function testConstructorWithProtectedNonStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodC');
        $this->name = 'name';
        $this->subject = new RealMethodDefinition($this->method, $this->name);

        $this->assertTrue($this->subject->isCallable());
        $this->assertFalse($this->subject->isStatic());
        $this->assertFalse($this->subject->isCustom());
        $this->assertSame('protected', $this->subject->accessLevel());
        $this->assertSame($this->name, $this->subject->name());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertNull($this->subject->callback());
    }

    public function testConstructorWithUncallable()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestInterfaceA::testClassAMethodA');
        $this->name = 'name';
        $this->subject = new RealMethodDefinition($this->method, $this->name);

        $this->assertFalse($this->subject->isCallable());
    }

    public function testConstructorDefaults()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodA');
        $this->subject = new RealMethodDefinition($this->method);

        $this->assertTrue($this->subject->isCallable());
        $this->assertTrue($this->subject->isStatic());
        $this->assertFalse($this->subject->isCustom());
        $this->assertSame('public', $this->subject->accessLevel());
        $this->assertSame('testClassAStaticMethodA', $this->subject->name());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertNull($this->subject->callback());
    }
}
