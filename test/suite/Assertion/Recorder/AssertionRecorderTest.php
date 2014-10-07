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

use Eloquent\Phony\Assertion\Exception\AssertionException;
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

    public function testCreateFailure()
    {
        $description = 'description';

        $this->assertEquals(new AssertionException($description), $this->subject->createFailure($description));
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
