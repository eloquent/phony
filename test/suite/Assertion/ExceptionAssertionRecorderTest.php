<?php

declare(strict_types=1);

namespace Eloquent\Phony\Assertion;

use AllowDynamicProperties;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class ExceptionAssertionRecorderTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new FacadeContainer();
        $this->subject = $container->assertionRecorder;
        $this->callVerifierFactory = $container->callVerifierFactory;
    }

    public function testCreateSuccess()
    {
        $events = [new ReturnedEvent(0, 0.0, null), new ReturnedEvent(1, 1.0, null)];
        $expected = new EventSequence($events, $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->createSuccess($events));
    }

    public function testCreateSuccessDefaults()
    {
        $expected = new EventSequence([], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->createSuccess());
    }

    public function testCreateSuccessFromEventCollection()
    {
        $events = new EventSequence([], $this->callVerifierFactory);

        $this->assertEquals($events, $this->subject->createSuccessFromEventCollection($events));
    }

    public function testCreateFailure()
    {
        $description = 'description';

        $this->expectException(AssertionException::class);
        $this->expectExceptionMessage($description);
        $this->subject->createFailure($description);
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
