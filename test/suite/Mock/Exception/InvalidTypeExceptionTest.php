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

class InvalidTypeExceptionTest extends PHPUnit_Framework_TestCase
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
}
