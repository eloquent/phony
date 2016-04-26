<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Reflection\FeatureDetector;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class WrappedTraitMethodTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockBuilderFactory = MockBuilderFactory::instance();
        $this->featureDetector = FeatureDetector::instance();

        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->callTraitMethod = new ReflectionMethod($this, 'setUp');
        $this->traitName = 'Eloquent\Phony\Test\TestTraitA';
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAMethodB');
        $this->mockBuilder = $this->mockBuilderFactory->create();
        $this->mock = $this->mockBuilder->partial();
        $this->handleFactory = HandleFactory::instance();
        $this->handle = $this->handleFactory->createStubbing($this->mock);
        $this->subject = new WrappedTraitMethod($this->callTraitMethod, $this->traitName, $this->method, $this->handle);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->traitName, $this->subject->traitName());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAMethodB', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, 'testClassAMethodB'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA');
        $this->handle = $this->handleFactory->createStubbingStatic($this->mockBuilder->build());
        $this->subject = new WrappedTraitMethod($this->callTraitMethod, $this->traitName, $this->method, $this->handle);

        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame('testClassAStaticMethodA', $this->subject->name());
        $this->assertSame($this->handle, $this->subject->handle());
        $this->assertNull($this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(
            array('Eloquent\Phony\Test\TestTraitA', 'testClassAStaticMethodA'),
            $this->subject->callback()
        );
        $this->assertNull($this->subject->label());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(null));
        $this->assertNull($this->subject->label());

        $this->subject->setLabel('label');

        $this->assertSame('label', $this->subject->label());
    }

    public function testInvokeMethods()
    {
        $traitName = 'Eloquent\Phony\Test\TestTraitA';
        $mockBuilder = $this->mockBuilderFactory->create($traitName);
        $class = $mockBuilder->build();
        $callTraitMethod = $class->getMethod('_callTrait');
        $callTraitMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAMethodB');
        $mock = $mockBuilder->get();
        $handle = $this->handleFactory->createStubbing($mock);
        $subject = new WrappedTraitMethod($callTraitMethod, $traitName, $method, $handle);

        $this->assertSame('ab', $subject('a', 'b'));
        $this->assertSame('ab', $subject->invoke('a', 'b'));
        $this->assertSame('ab', $subject->invokeWith(array('a', 'b')));
    }

    public function testInvokeMethodsWithStatic()
    {
        $traitName = 'Eloquent\Phony\Test\TestTraitA';
        $mockBuilder = $this->mockBuilderFactory->create($traitName);
        $class = $mockBuilder->build();
        $callTraitMethod = $class->getMethod('_callTraitStatic');
        $callTraitMethod->setAccessible(true);
        $method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA');
        $handle = $this->handleFactory->createStubbingStatic($mockBuilder->build());
        $subject = new WrappedTraitMethod($callTraitMethod, $traitName, $method, $handle);
        $a = 'a';

        $this->assertSame('ab', $subject->invokeWith(array(&$a, 'b')));
    }
}
