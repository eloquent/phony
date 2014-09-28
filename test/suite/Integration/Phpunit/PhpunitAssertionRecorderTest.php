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

    public function testRecordSuccess()
    {
        $beforeCount = PHPUnit_Framework_Assert::getCount();
        $this->subject->recordSuccess();
        $afterCount = PHPUnit_Framework_Assert::getCount();

        $this->assertSame($beforeCount + 1, $afterCount);
    }

    public function testCreateFailure()
    {
        $description = 'description';

        $this->assertEquals(
            new PHPUnit_Framework_ExpectationFailedException($description),
            $this->subject->createFailure($description)
        );
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
