<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class InvalidDefinitionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $name = 111;
        $value = 'value';
        $cause = new Exception();
        $exception = new InvalidDefinitionException($name, $value, $cause);

        $this->assertSame($name, $exception->name());
        $this->assertSame($value, $exception->value());
        $this->assertSame("Invalid mock definition 111 => (string).", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
