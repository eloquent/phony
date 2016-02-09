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

use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedResponseExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $message = 'You done goofed.';
        $cause = new Exception();
        $exception = new UndefinedResponseException($message, $cause);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
