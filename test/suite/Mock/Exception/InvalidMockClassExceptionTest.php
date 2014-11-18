<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class InvalidMockClassExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $value = 111;
        $cause = new Exception();
        $exception = new InvalidMockClassException($value, $cause);

        $this->assertSame($value, $exception->value());
        $this->assertSame("Value of type 'integer' is not a mock class.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
