<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Collection\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedIndexExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $index = 111;
        $cause = new Exception();
        $exception = new UndefinedIndexException($index, $cause);

        $this->assertSame($index, $exception->index());
        $this->assertSame('Undefined index 111.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
