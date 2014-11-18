<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Recorder;

use Eloquent\Phony\Call\Event\CallEventCollection;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class AssertionRecorderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new AssertionRecorder();
    }

    public function testCreateSuccess()
    {
        $events = array(new ReturnedEvent(0, 0.0), new ReturnedEvent(1, 1.0));
        $expected = new CallEventCollection($events);

        $this->assertEquals($expected, $this->subject->createSuccess($events));
    }

    public function testCreateSuccessDefaults()
    {
        $expected = new CallEventCollection();

        $this->assertEquals($expected, $this->subject->createSuccess());
    }

    public function testCreateFailure()
    {
        $description = 'description';

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $description);
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
