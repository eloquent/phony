<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit_Framework_TestCase;

class FinalMethodStubExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $className = 'ClassName';
        $name = 'method';
        $exception = new FinalMethodStubException($className, $name);

        $this->assertSame($className, $exception->className());
        $this->assertSame($name, $exception->name());
        $this->assertSame('The method ClassName::method() cannot be stubbed because it is final.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
