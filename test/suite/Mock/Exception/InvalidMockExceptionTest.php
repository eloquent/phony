<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit_Framework_TestCase;

class InvalidMockExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $value = 111;
        $exception = new InvalidMockException($value);

        $this->assertSame($value, $exception->value());
        $this->assertSame("Value of type 'integer' is not a mock.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithObject()
    {
        $value = (object) array();
        $exception = new InvalidMockException($value);

        $this->assertSame($value, $exception->value());
        $this->assertSame("Object of type 'stdClass' is not a mock.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
