<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle\Factory;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Handle\Stubbing\StaticStubbingHandle;
use Eloquent\Phony\Mock\Handle\Stubbing\StubbingHandle;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionProperty;

class HandleFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->stubFactory = new StubFactory();
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->assertionRenderer = new AssertionRenderer();
        $this->assertionRecorder = new AssertionRecorder();
        $this->invoker = new Invoker();
        $this->subject = new HandleFactory(
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->stubFactory, $this->subject->stubFactory());
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->invoker, $this->subject->invoker());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new HandleFactory();

        $this->assertSame(StubFactory::instance(), $this->subject->stubFactory());
        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
    }

    public function testCreateStubbingNew()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->full();
        $handleProperty = new ReflectionProperty($mock, '_handle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue($mock, null);
        $expected = new StubbingHandle(
            $mock,
            (object) array(
                'defaultAnswerCallback' => 'Eloquent\Phony\Stub\Stub::returnsNullAnswerCallback',
                'stubs' => (object) array(),
                'label' => 'label',
            ),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $actual = $this->subject->createStubbing($mock, 'label');

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingAdapt()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->full();
        $handleProperty = new ReflectionProperty($mock, '_handle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue($mock);
        $actual = $this->subject->createStubbing($mock);

        $this->assertSame($expected, $actual);
        $this->assertSame($actual, $this->subject->createStubbing($actual));
    }

    public function testCreateStubbingFromVerifier()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->full();
        $handleProperty = new ReflectionProperty($mock, '_handle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue($mock);
        $verificationHandle = $this->subject->createVerification($mock);
        $actual = $this->subject->createStubbing($verificationHandle);

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
        $mock = $mockBuilder->full();
        $handleProperty = new ReflectionProperty($mock, '_handle');
        $handleProperty->setAccessible(true);
        $stubbingHandle = $handleProperty->getValue($mock);
        $actual = $this->subject->createVerification($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\VerificationHandle', $actual);
        $this->assertSame($stubbingHandle->mock(), $actual->mock());
        $this->assertSame($stubbingHandle->stubs(), $actual->stubs());
        $this->assertSame($stubbingHandle->label(), $actual->label());
    }

    public function testCreateVerificationAdapt()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->full();
        $actual = $this->subject->createVerification($mock);

        $this->assertSame($actual, $this->subject->createVerification($actual));
    }

    public function testCreateVerificationFromStubbing()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->full();
        $stubbingHandle = $this->subject->createStubbing($mock);
        $actual = $this->subject->createVerification($mock);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\VerificationHandle', $actual);
        $this->assertSame($stubbingHandle->mock(), $actual->mock());
        $this->assertSame($stubbingHandle->stubs(), $actual->stubs());
        $this->assertSame($stubbingHandle->label(), $actual->label());
    }

    public function testCreateStubbingStaticNew()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue(null, null);
        $expected = new StaticStubbingHandle(
            $class,
            null,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $actual = $this->subject->createStubbingStatic($class);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateStubbingStaticAdapt()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $actual = $this->subject->createStubbingStatic($class);

        $this->assertSame($expected, $actual);
        $this->assertSame($actual, $this->subject->createStubbingStatic($actual));
    }

    public function testCreateStubbingStaticFromVerifier()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $verificationHandle = $this->subject->createVerificationStatic($class);
        $actual = $this->subject->createStubbingStatic($verificationHandle);

        $this->assertSame($expected, $actual);
    }

    public function testCreateStubbingStaticFromMock()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $actual = $this->subject->createStubbingStatic($mockBuilder->partial());

        $this->assertSame($expected, $actual);
    }

    public function testCreateStubbingStaticFromSting()
    {
        $mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
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
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $stubbingHandle = $handleProperty->getValue(null);
        $actual = $this->subject->createVerificationStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandle', $actual);
        $this->assertSame($stubbingHandle->clazz(), $actual->clazz());
        $this->assertSame($stubbingHandle->stubs(), $actual->stubs());
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
        $stubbingHandle = $this->subject->createStubbingStatic($class);
        $actual = $this->subject->createVerificationStatic($class);

        $this->assertInstanceOf('Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandle', $actual);
        $this->assertSame($stubbingHandle->clazz(), $actual->clazz());
        $this->assertSame($stubbingHandle->stubs(), $actual->stubs());
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
