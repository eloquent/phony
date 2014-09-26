<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Test\TestAssertionException;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class AssertionRecorderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new AssertionRecorder();
    }

    public function testRecordSuccess()
    {
        $this->assertNull($this->subject->recordSuccess());
    }

    public function testRecordFailure()
    {
        $failure = new TestAssertionException();
        $exception = null;
        try {
            $this->subject->recordFailure($failure);
        } catch (Exception $exception) {}

        $this->assertSame($failure, $exception);
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
