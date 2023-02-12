<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

use Countable;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Exception\InvalidMockClassException;
use Eloquent\Phony\Mock\Exception\InvalidMockException;
use Eloquent\Phony\Mock\Exception\NonMockClassException;
use Eloquent\Phony\Stub\EmptyValueFactory;
use Eloquent\Phony\Stub\StubData;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\WithDynamicProperties;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class HandleFactoryTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->stubFactory = StubFactory::instance();
        $this->stubVerifierFactory = StubVerifierFactory::instance();
        $this->emptyValueFactory = EmptyValueFactory::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->invoker = new Invoker();
        $this->subject = new HandleFactory(
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $this->mockBuilderFactory = MockBuilderFactory::instance();
    }

    public function testInstanceHandleNew()
    {
        $mockBuilder = $this->mockBuilderFactory->create(TestClassB::class);
        $mock = $mockBuilder->full();
        $handleProperty = new ReflectionProperty($mock, '_handle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue($mock, null);
        $expected = new InstanceHandle(
            $mock,
            (object) [
                'defaultAnswerCallback' => [StubData::class, 'returnsEmptyAnswerCallback'],
                'stubs' => (object) [],
                'isRecording' => true,
                'label' => 'label',
            ],
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
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
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
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
