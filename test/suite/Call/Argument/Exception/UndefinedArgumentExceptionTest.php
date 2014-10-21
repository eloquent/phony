<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Argument\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $index = 111;
        $cause = new Exception();
        $exception = new UndefinedArgumentException($index, $cause);

        $this->assertSame($index, $exception->index());
        $this->assertSame("No argument defined for index 111.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
