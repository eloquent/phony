<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
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
        $this->proxyFactory = new ProxyFactory();
        $this->proxy = $this->proxyFactory->createStubbing($this->mock);
        $this->subject = new WrappedTraitMethod($this->callTraitMethod, $this->traitName, $this->method, $this->proxy);
    }

    public function testConstructor()
    {
        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->traitName, $this->subject->traitName());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame($this->proxy, $this->subject->proxy());
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame(array($this->mock, 'testClassAMethodB'), $this->subject->callback());
        $this->assertNull($this->subject->label());
    }

    public function testConstructorWithStatic()
    {
        $this->method = new ReflectionMethod('Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA');
        $this->proxy = $this->proxyFactory->createStubbingStatic($this->mockBuilder->build());
        $this->subject = new WrappedTraitMethod($this->callTraitMethod, $this->traitName, $this->method, $this->proxy);

        $this->assertSame($this->callTraitMethod, $this->subject->callTraitMethod());
        $this->assertSame($this->method, $this->subject->method());
        $this->assertSame($this->proxy, $this->subject->proxy());
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
        $this->subject->setLabel(null);

        $this->assertNull($this->subject->label());

        $this->subject->setLabel('label');

        $this->assertSame('label', $this->subject->label());
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
        $proxy = $this->proxyFactory->createStubbing($mock);
        $subject = new WrappedTraitMethod($callTraitMethod, $traitName, $method, $proxy);

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
        $proxy = $this->proxyFactory->createStubbingStatic($mockBuilder->build());
        $subject = new WrappedTraitMethod($callTraitMethod, $traitName, $method, $proxy);
        $a = 'a';

        $this->assertSame('ab', $subject->invokeWith(array(&$a, 'b')));
    }
}
