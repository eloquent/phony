<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Event\EventSequence;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ExceptionAssertionRecorderTest extends TestCase
{
    protected function setUp()
    {
        $this->subject = new ExceptionAssertionRecorder();

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->subject->setCallVerifierFactory($this->callVerifierFactory);
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

        $this->expectException(AssertionException::class, $description);
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
