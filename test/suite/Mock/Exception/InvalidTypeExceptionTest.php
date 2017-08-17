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

use Exception;
use PHPUnit\Framework\TestCase;

class InvalidTypeExceptionTest extends TestCase
{
    public function testException()
    {
        $type = null;
        $cause = new Exception();
        $exception = new InvalidTypeException($type, $cause);

        $this->assertSame($type, $exception->type());
        $this->assertSame("Unable to add type of type 'NULL'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }

    public function testExceptionWithString()
    {
        $type = 'Nonexistent';
        $cause = new Exception();
        $exception = new InvalidTypeException($type, $cause);

        $this->assertSame($type, $exception->type());
        $this->assertSame("Undefined type 'Nonexistent'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
