<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionProperty;

class HandleFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->stubFactory = StubFactory::instance();
        $this->stubVerifierFactory = StubVerifierFactory::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->invoker = new Invoker();
        $this->subject = new HandleFactory(
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $this->mockBuilderFactory = MockBuilderFactory::instance();
    }

    public function testInstanceHandleNew()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->full();
        $handleProperty = new ReflectionProperty($mock, '_handle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue($mock, null);
        $expected = new InstanceHandle(
            $mock,
            (object) array(
                'defaultAnswerCallback' => 'Eloquent\Phony\Stub\StubData::returnsEmptyAnswerCallback',
                'stubs' => (object) array(),
                'isRecording' => true,
                'label' => 'label',
            ),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $actual = $this->subject->instanceHandle($mock, 'label');

        $this->assertEquals($expected, $actual);
    }

    public function testInstanceHandleAdapt()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $mock = $mockBuilder->full();
        $handleProperty = new ReflectionProperty($mock, '_handle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue($mock);
        $actual = $this->subject->instanceHandle($mock);

        $this->assertSame($expected, $actual);
        $this->assertSame($actual, $this->subject->instanceHandle($actual));
    }

    public function testInstanceHandleFailureInvalid()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidMockException');
        $this->subject->instanceHandle(null);
    }

    public function testStaticHandleNew()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue(null, null);
        $expected = new StaticHandle(
            $class,
            (object) array(
                'defaultAnswerCallback' => 'Eloquent\Phony\Stub\StubData::forwardsAnswerCallback',
                'stubs' => (object) array(),
                'isRecording' => true,
            ),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $actual = $this->subject->staticHandle($class);

        $this->assertEquals($expected, $actual);
    }

    public function testStaticHandleAdapt()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $actual = $this->subject->staticHandle($class);

        $this->assertSame($expected, $actual);
        $this->assertSame($actual, $this->subject->staticHandle($actual));
    }

    public function testStaticHandleFromMock()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $actual = $this->subject->staticHandle($mockBuilder->partial());

        $this->assertSame($expected, $actual);
    }

    public function testStaticHandleFromSting()
    {
        $mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $actual = $this->subject->staticHandle($class->getName());

        $this->assertSame($expected, $actual);
    }

    public function testStaticHandleFailureUndefinedClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->staticHandle('Undefined');
    }

    public function testStaticHandleFailureNonMockClass()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->staticHandle(new ReflectionClass('stdClass'));
    }

    public function testStaticHandleFailureNonMockClassString()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\NonMockClassException');
        $this->subject->staticHandle('Countable');
    }

    public function testStaticHandleFailureInvalid()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\InvalidMockClassException');
        $this->subject->staticHandle(null);
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
