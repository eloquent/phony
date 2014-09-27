<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class AssertionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $message = 'message';
        $cause = new Exception();
        $exception = new AssertionException($message, $cause);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
