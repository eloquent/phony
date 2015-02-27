<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use Eloquent\Phony\Call\Event\CallEventCollection;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use SimpleReporter;
use SimpleTest;
use SimpleTestContext;

class SimpletestAssertionRecorderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->simpletestContext = new SimpleTestContext();
        $this->simpletestReporter = new SimpleReporter();
        $this->simpletestContext->setReporter($this->simpletestReporter);
        $this->subject = new SimpletestAssertionRecorder($this->simpletestContext);
    }

    public function testConstructor()
    {
        $this->assertSame($this->simpletestContext, $this->subject->simpletestContext());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SimpletestAssertionRecorder();

        $this->assertSame(SimpleTest::getContext(), $this->subject->simpletestContext());
    }

    public function testCreateSuccess()
    {
        $events = array(new ReturnedEvent(0, 0.0), new ReturnedEvent(1, 1.0));
        $expected = new CallEventCollection($events);
        $beforeCount = $this->simpletestReporter->getPassCount();
        $actual = $this->subject->createSuccess($events);
        $afterCount = $this->simpletestReporter->getPassCount();

        $this->assertEquals($expected, $actual);
        $this->assertSame($beforeCount + 1, $afterCount);
    }

    public function testCreateSuccessDefaults()
    {
        $expected = new CallEventCollection();
        $beforeCount = $this->simpletestReporter->getPassCount();
        $actual = $this->subject->createSuccess();
        $afterCount = $this->simpletestReporter->getPassCount();

        $this->assertEquals($expected, $actual);
        $this->assertSame($beforeCount + 1, $afterCount);
    }

    public function testCreateFailure()
    {
        $beforeCount = $this->simpletestReporter->getFailCount();
        $this->subject->createFailure('description');
        $afterCount = $this->simpletestReporter->getFailCount();

        $this->assertSame($beforeCount + 1, $afterCount);
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
