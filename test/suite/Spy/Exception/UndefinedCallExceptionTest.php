<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedCallExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $index = 111;
        $cause = new Exception();
        $exception = new UndefinedCallException($index, $cause);

        $this->assertSame($index, $exception->index());
        $this->assertSame("No call defined for index 111.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
