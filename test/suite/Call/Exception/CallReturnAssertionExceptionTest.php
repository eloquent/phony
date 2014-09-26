<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Exception;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Exception;
use PHPUnit_Framework_TestCase;

class CallReturnAssertionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $call = new Call(array(), 'anotherValue', 0, 1.11, 2.22);
        $matcher = new EqualToMatcher('value');
        $cause = new Exception();
        $exception = new CallReturnAssertionException($call, $matcher, $cause);

        $this->assertSame($call, $exception->call());
        $this->assertSame($matcher, $exception->matcher());
        $this->assertSame("The return value did not match <is equal to <string:value>>.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
