<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Exception;

use PHPUnit_Framework_TestCase;

class UndefinedArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $index = 111;
        $exception = new UndefinedArgumentException($index);

        $this->assertSame($index, $exception->index());
        $this->assertSame('No argument defined for index 111.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
