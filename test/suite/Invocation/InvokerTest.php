<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Test\TestInvocable;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class InvokerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new Invoker();

        $this->invocable = new TestInvocable();
    }

    public function testCallWith()
    {
        $this->assertSame(phpversion(), $this->subject->callWith('phpversion'));
        $this->assertSame(1, $this->subject->callWith('strlen', array('a')));
        $this->assertSame(
            array('invokeWith', array('a', 'b')),
            $this->subject->callWith($this->invocable, array('a', 'b'))
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
