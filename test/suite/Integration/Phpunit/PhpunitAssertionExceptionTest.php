<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Test\TestAssertionException;
use Exception;
use PHPUnit_Framework_TestCase;

class PhpunitAssertionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $failure = new TestAssertionException('message');
        $cause = new Exception();
        $exception = new PhpunitAssertionException($failure, $cause);

        $this->assertSame($failure, $exception->failure());
        $this->assertSame("message", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
