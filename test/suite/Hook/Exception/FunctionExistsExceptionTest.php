<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Hook\Exception;

use PHPUnit_Framework_TestCase;

class FunctionExistsExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $functionName = 'functionName';
        $exception = new FunctionExistsException($functionName);

        $this->assertSame($functionName, $exception->functionName());
        $this->assertSame("Function 'functionName' is already defined.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
