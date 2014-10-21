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
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxy;
use Eloquent\Phony\Mock\Proxy\Stubbing\StubbingProxy;
use Eloquent\Phony\Mock\Proxy\Verification\StaticVerificationProxy;
use Eloquent\Phony\Mock\Proxy\Verification\VerificationProxy;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionProperty;

class ProxyFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockFactory = new MockFactory();
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->wildcardMatcher = new WildcardMatcher();
        $this->subject = new ProxyFactory($this->mockFactory, $this->stubVerifierFactory, $this->wildcardMatcher);
    }

    public function testConstructor()
    {
        $this->assertSame($this->mockFactory, $this->subject->mockFactory());
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
        $this->assertSame($this->wildcardMatcher, $this->subject->wildcardMatcher());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new ProxyFactory();

        $this->assertSame(MockFactory::instance(), $this->subject->mockFactory());
        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
        $this->assertSame(WildcardMatcher::instance(), $this->subject->wildcardMatcher());
    }

    public function testCreateStubbingStatic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticStubbingProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbingStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingStaticNonMagic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = null;
        $expected = new StaticStubbingProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbingStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingStaticWithObject()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->get();
        $class = new ReflectionClass($mock);
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticStubbingProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbingStatic($mock);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingStaticWithString()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticStubbingProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbingStatic($class->getName());

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingStaticWithProxy()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticStubbingProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbingStatic($this->subject->createVerification($mockBuilder->get()));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingStaticFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStubbingStatic('Nonexistent');
    }

    public function testCreateStubbingStaticFailureNonMockClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStubbingStatic(__CLASS__);
    }

    public function testCreateStubbing()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $class = new ReflectionClass($mock);
        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StubbingProxy(
            $mock,
            $class,
            $stubsProperty->getValue($mock),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbing($mock);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingNonMagic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $mock = $mockBuilder->create();
        $class = new ReflectionClass($mock);
        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = null;
        $expected = new StubbingProxy(
            $mock,
            $class,
            $stubsProperty->getValue($mock),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbing($mock);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingWithProxy()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $class = new ReflectionClass($mock);
        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StubbingProxy(
            $mock,
            $class,
            $stubsProperty->getValue($mock),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createStubbing($this->subject->createVerification($mock));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingFailureNonMock()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createStubbing($this);
    }

    public function testCreateVerificationStatic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticVerificationProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerificationStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationStaticNonMagic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = null;
        $expected = new StaticVerificationProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerificationStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationStaticWithObject()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->get();
        $class = new ReflectionClass($mock);
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticVerificationProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerificationStatic($mock);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationStaticWithString()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticVerificationProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerificationStatic($class->getName());

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationStaticWithProxy()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build();
        $stubsProperty = $class->getProperty('_staticStubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStaticStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new StaticVerificationProxy(
            $class,
            $stubsProperty->getValue(null),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerificationStatic($this->subject->createStubbing($mockBuilder->get()));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationStaticFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createVerificationStatic('Nonexistent');
    }

    public function testCreateVerificationStaticFailureNonMockClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createVerificationStatic(__CLASS__);
    }

    public function testCreateVerification()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $class = new ReflectionClass($mock);
        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new VerificationProxy(
            $mock,
            $class,
            $stubsProperty->getValue($mock),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerification($mock);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationNonMagic()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $mock = $mockBuilder->create();
        $class = new ReflectionClass($mock);
        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = null;
        $expected = new VerificationProxy(
            $mock,
            $class,
            $stubsProperty->getValue($mock),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerification($mock);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationWithProxy()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->create();
        $class = new ReflectionClass($mock);
        $stubsProperty = new ReflectionProperty($mock, '_stubs');
        $stubsProperty->setAccessible(true);
        $magicStubsProperty = $class->getProperty('_magicStubs');
        $magicStubsProperty->setAccessible(true);
        $expected = new VerificationProxy(
            $mock,
            $class,
            $stubsProperty->getValue($mock),
            $magicStubsProperty,
            $this->mockFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );
        $actual = $this->subject->createVerification($this->subject->createStubbing($mock));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateVerificationFailureNonMock()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->createVerification($this);
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
