<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Factory;

use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxy;
use Eloquent\Phony\Mock\Proxy\Stubbing\StubbingProxy;
use Eloquent\Phony\Mock\Proxy\Verification\StaticVerificationProxy;
use Eloquent\Phony\Mock\Proxy\Verification\VerificationProxy;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionProperty;

class ProxyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->stubFactory = new StubFactory();
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->wildcardMatcher = new WildcardMatcher();
        $this->subject = new ProxyFactory($this->stubFactory, $this->stubVerifierFactory, $this->wildcardMatcher);
    }

    public function testConstructor()
    {
        $this->assertSame($this->stubFactory, $this->subject->stubFactory());
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
        $this->assertSame($this->wildcardMatcher, $this->subject->wildcardMatcher());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new ProxyFactory();

        $this->assertSame(StubFactory::instance(), $this->subject->stubFactory());
        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
        $this->assertSame(WildcardMatcher::instance(), $this->subject->wildcardMatcher());
    }

    public function testCreateStubbingNew()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $proxyProperty = new ReflectionProperty($mock, '_proxy');
        $proxyProperty->setAccessible(true);
        $proxyProperty->setValue($mock, null);
        $expected = new StubbingProxy(
            $mock,
            null,
            'id',
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbing($mock, 'id');

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingAdapt()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $proxyProperty = new ReflectionProperty($mock, '_proxy');
        $proxyProperty->setAccessible(true);
        $expected = $proxyProperty->getValue($mock);
        $actual = $this->subject->createStubbing($mock);

        $this->assertSame($expected, $actual);
        $this->assertSame($actual, $this->subject->createStubbing($actual));
    }

    public function testCreateStubbingFromVerifier()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $proxyProperty = new ReflectionProperty($mock, '_proxy');
        $proxyProperty->setAccessible(true);
        $expected = $proxyProperty->getValue($mock);
        $verifierProxy = $this->subject->createVerification($mock);
        $actual = $this->subject->createStubbing($verifierProxy);

        $this->assertSame($expected, $actual);
    }

    public function testCreateStubbingFailureInvalid()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidMockException');
        $this->subject->createStubbing(null);
    }

    public function testCreateVerification()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $proxyProperty = new ReflectionProperty($mock, '_proxy');
        $proxyProperty->setAccessible(true);
        $stubbingProxy = $proxyProperty->getValue($mock);
        $actual = $this->subject->createVerification($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Proxy\Verification\VerificationProxy', $actual);
        $this->assertSame($stubbingProxy->mock(), $actual->mock());
        $this->assertSame($stubbingProxy->stubs(), $actual->stubs());
        $this->assertSame($stubbingProxy->isFull(), $actual->isFull());
        $this->assertSame($stubbingProxy->id(), $actual->id());
    }

    public function testCreateVerificationAdapt()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $actual = $this->subject->createVerification($mock);

        $this->assertSame($actual, $this->subject->createVerification($actual));
    }

    public function testCreateVerificationFromStubbing()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $stubbingProxy = $this->subject->createStubbing($mock);
        $actual = $this->subject->createVerification($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Proxy\Verification\VerificationProxy', $actual);
        $this->assertSame($stubbingProxy->mock(), $actual->mock());
        $this->assertSame($stubbingProxy->stubs(), $actual->stubs());
        $this->assertSame($stubbingProxy->isFull(), $actual->isFull());
        $this->assertSame($stubbingProxy->id(), $actual->id());
    }

    public function testCreateStubbingStaticNew()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $proxyProperty->setValue(null, null);
        $expected = new StaticStubbingProxy(
            $class,
            null,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbingStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingStaticAdapt()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $expected = $proxyProperty->getValue(null);
        $actual = $this->subject->createStubbingStatic($class);

        $this->assertSame($expected, $actual);
        $this->assertSame($actual, $this->subject->createStubbingStatic($actual));
    }

    public function testCreateStubbingStaticFromVerifier()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $expected = $proxyProperty->getValue(null);
        $verifierProxy = $this->subject->createVerificationStatic($class);
        $actual = $this->subject->createStubbingStatic($verifierProxy);

        $this->assertSame($expected, $actual);
    }

    public function testCreateStubbingStaticFromMock()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $expected = $proxyProperty->getValue(null);
        $actual = $this->subject->createStubbingStatic($mockBuilder->create());

        $this->assertSame($expected, $actual);
    }

    public function testCreateStubbingStaticFromSting()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $expected = $proxyProperty->getValue(null);
        $actual = $this->subject->createStubbingStatic($class->getName());

        $this->assertSame($expected, $actual);
    }

    public function testCreateStubbingStaticFailureUndefinedClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStubbingStatic('Undefined');
    }

    public function testCreateStubbingStaticFailureNonMockClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStubbingStatic(new ReflectionClass('stdClass'));
    }

    public function testCreateStubbingStaticFailureNonMockClassString()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStubbingStatic('Countable');
    }

    public function testCreateStubbingStaticFailureInvalid()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidMockClassException');
        $this->subject->createStubbingStatic(null);
    }

    public function testCreateVerificationStatic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $stubbingProxy = $proxyProperty->getValue(null);
        $actual = $this->subject->createVerificationStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Proxy\Verification\StaticVerificationProxy', $actual);
        $this->assertSame($stubbingProxy->clazz(), $actual->clazz());
        $this->assertSame($stubbingProxy->stubs(), $actual->stubs());
        $this->assertSame($stubbingProxy->isFull(), $actual->isFull());
    }

    public function testCreateVerificationStaticAdapt()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $actual = $this->subject->createVerificationStatic($class);

        $this->assertSame($actual, $this->subject->createVerificationStatic($actual));
    }

    public function testCreateVerificationStaticFromStubbing()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $stubbingProxy = $this->subject->createStubbingStatic($class);
        $actual = $this->subject->createVerificationStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Proxy\Verification\StaticVerificationProxy', $actual);
        $this->assertSame($stubbingProxy->clazz(), $actual->clazz());
        $this->assertSame($stubbingProxy->stubs(), $actual->stubs());
        $this->assertSame($stubbingProxy->isFull(), $actual->isFull());
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $stubsProperty = $reflector->getProperty('instance');
        $stubsProperty->setAccessible(true);
        $stubsProperty->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
