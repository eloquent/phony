<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Exception;

use PHPUnit_Framework_TestCase;

class UndefinedResponseExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $message = 'You done goofed.';
        $exception = new UndefinedResponseException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
