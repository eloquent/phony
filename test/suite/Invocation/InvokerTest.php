<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Test\TestInvocable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InvokerTest extends TestCase
{
    protected function setUp()
    {
        $this->subject = new Invoker();

        $this->invocable = new TestInvocable();
    }

    public function testCallWith()
    {
        $this->assertSame(phpversion(), $this->subject->callWith('phpversion', Arguments::create()));
        $this->assertSame(1, $this->subject->callWith('strlen', Arguments::create('a')));
        $this->assertSame(
            ['invokeWith', ['a', 'b']],
            $this->subject->callWith($this->invocable, Arguments::create('a', 'b'))
        );
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
