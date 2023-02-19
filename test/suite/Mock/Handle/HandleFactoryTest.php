<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

use AllowDynamicProperties;
use Countable;
use Eloquent\Phony\Mock\Exception\InvalidMockClassException;
use Eloquent\Phony\Mock\Exception\InvalidMockException;
use Eloquent\Phony\Mock\Exception\NonMockClassException;
use Eloquent\Phony\Stub\StubData;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassB;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

#[AllowDynamicProperties]
class HandleFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->handleFactory;
        $this->mockBuilderFactory = $this->container->mockBuilderFactory;
    }

    public function testInstanceHandleNew()
    {
        $mockBuilder = $this->mockBuilderFactory->create(TestClassB::class);
        $class = $mockBuilder->build(true);
        $mock = $class->newInstanceWithoutConstructor();
        $expected = new InstanceHandle(
            $mock,
            (object) [
                'defaultAnswerCallback' => [StubData::class, 'returnsEmptyAnswerCallback'],
                'stubs' => (object) [],
                'isRecording' => true,
                'label' => 'label',
            ],
            $this->container->stubFactory,
            $this->container->stubVerifierFactory,
            $this->container->emptyValueFactory,
            $this->container->assertionRenderer,
            $this->container->assertionRecorder,
            $this->container->invoker
        );
        $actual = $this->subject->instanceHandle($mock, 'label');

        $this->assertEquals($expected, $actual);
    }

    public function testInstanceHandleAdapt()
    {
        $mockBuilder = $this->mockBuilderFactory->create(TestClassB::class);
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
        $this->expectException(InvalidMockException::class);
        $this->subject->instanceHandle(null);
    }

    public function testStaticHandleNew()
    {
        $mockBuilder = $this->mockBuilderFactory->create(TestClassB::class);
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue(null, null);
        $expected = new StaticHandle(
            $class,
            (object) [
                'defaultAnswerCallback' => [StubData::class, 'forwardsAnswerCallback'],
                'stubs' => (object) [],
                'isRecording' => true,
            ],
            $this->container->stubFactory,
            $this->container->stubVerifierFactory,
            $this->container->emptyValueFactory,
            $this->container->assertionRenderer,
            $this->container->assertionRecorder,
            $this->container->invoker
        );
        $actual = $this->subject->staticHandle($class);

        $this->assertEquals($expected, $actual);
    }

    public function testStaticHandleAdapt()
    {
        $mockBuilder = $this->mockBuilderFactory->create(TestClassB::class);
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
        $mockBuilder = $this->mockBuilderFactory->create(TestClassB::class);
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $actual = $this->subject->staticHandle($mockBuilder->partial());

        $this->assertSame($expected, $actual);
    }

    public function testStaticHandleFromSting()
    {
        $mockBuilder = $this->mockBuilderFactory->create(TestClassB::class);
        $class = $mockBuilder->build(true);
        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $expected = $handleProperty->getValue(null);
        $actual = $this->subject->staticHandle($class->getName());

        $this->assertSame($expected, $actual);
    }

    public function testStaticHandleFailureUndefinedClass()
    {
        $this->expectException(NonMockClassException::class);
        $this->subject->staticHandle(Undefined::class);
    }

    public function testStaticHandleFailureNonMockClass()
    {
        $this->expectException(NonMockClassException::class);
        $this->subject->staticHandle(new ReflectionClass(stdClass::class));
    }

    public function testStaticHandleFailureNonMockClassString()
    {
        $this->expectException(NonMockClassException::class);
        $this->subject->staticHandle(Countable::class);
    }

    public function testStaticHandleFailureInvalid()
    {
        $this->expectException(InvalidMockClassException::class);
        $this->subject->staticHandle(null);
    }
}
