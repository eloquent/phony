<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Exception;

use PHPUnit_Framework_TestCase;

class FunctionSignatureMismatchExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $functionName = 'functionName';
        $exception = new FunctionSignatureMismatchException($functionName);

        $this->assertSame($functionName, $exception->functionName());
        $this->assertSame(
            "Function 'functionName' has a different signature to the supplied callback.",
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
