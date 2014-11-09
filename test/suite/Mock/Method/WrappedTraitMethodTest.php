<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class WrappedTraitMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->featureDetector = FeatureDetector::instance();

        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->callTraitMethod = new ReflectionMethod($this, 'setUp');
        $this->traitName = 'Eloquent\Phony\Test\TestTraitA';
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAMethodB');
        $this->mockBuilder = new MockBuilder();
        $this->mock = $this->mockBuilder->create();
        $this->subject = new WrappedTraitMethod($this->callTraitMethod, $this->traitName, $this->method, $this->mock);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->traitName, $this->subject->traitName());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, 'testClassAMethodB'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA');
        $this->subject = new WrappedTraitMethod($this->callTraitMethod, $this->traitName, $this->method);

        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertNull($this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestTraitA', 'testClassAStaticMethodA'),
            $this->subject->callback()
        );
        $this->assertNull($this->subject->label());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new WrappedTraitMethod($this->callTraitMethod, $this->traitName, $this->method);

        $this->assertNull($this->subject->mock());
        $this->assertSame(array(null, $this->method->getName()), $this->subject->callback());
    }

    public function testInvokeMethods()
    {
        $traitName = 'Eloquent\Phony\Test\TestTraitA';
        $mockBuilder = new MockBuilder($traitName);
        $class = $mockBuilder->build();
        $callTraitMethod = $class->getMethod('_callTrait');
        $callTraitMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAMethodB');
        $mock = $mockBuilder->get();
        $subject = new WrappedTraitMethod($callTraitMethod, $traitName, $method, $mock);

        $this->assertSame('ab', $subject('a', 'b'));
        $this->assertSame('ab', $subject->invoke('a', 'b'));
        $this->assertSame('ab', $subject->invokeWith(array('a', 'b')));
    }

    public function testInvokeMethodsWithStatic()
    {
        $traitName = 'Eloquent\Phony\Test\TestTraitA';
        $mockBuilder = new MockBuilder($traitName);
        $class = $mockBuilder->build();
        $callTraitMethod = $class->getMethod('_callTraitStatic');
        $callTraitMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA');
        $subject = new WrappedTraitMethod($callTraitMethod, $traitName, $method);
        $a = 'a';

        $this->assertSame('ab', $subject->invokeWith(array(&$a, 'b')));
    }
}
