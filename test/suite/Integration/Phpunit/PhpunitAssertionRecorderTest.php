<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Event\EventCollection;
use PHPUnit_Framework_Assert;
use PHPUnit_Framework_ExpectationFailedException;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class PhpunitAssertionRecorderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new PhpunitAssertionRecorder();
    }

    public function testCreateSuccess()
    {
        $events = array(new ReturnedEvent(0, 0.0), new ReturnedEvent(1, 1.0));
        $expected = new EventCollection($events);
        $beforeCount = PHPUnit_Framework_Assert::getCount();
        $actual = $this->subject->createSuccess($events);
        $afterCount = PHPUnit_Framework_Assert::getCount();

        $this->assertEquals($expected, $actual);
        $this->assertSame($beforeCount + 1, $afterCount);
    }

    public function testCreateSuccessDefaults()
    {
        $expected = new EventCollection();
        $beforeCount = PHPUnit_Framework_Assert::getCount();
        $actual = $this->subject->createSuccess();
        $afterCount = PHPUnit_Framework_Assert::getCount();

        $this->assertEquals($expected, $actual);
        $this->assertSame($beforeCount + 1, $afterCount);
    }

    public function testCreateFailure()
    {
        $description = 'description';
        $actual = $this->subject->createFailure($description);

        $this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $actual);
        $this->assertSame($description, $actual->getMessage());
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
