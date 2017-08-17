<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedMethodStubExceptionTest extends TestCase
{
    public function testException()
    {
        $className = 'ClassName';
        $name = 'method';
        $exception = new UndefinedMethodStubException($className, $name);

        $this->assertSame($className, $exception->className());
        $this->assertSame($name, $exception->name());
        $this->assertSame('The requested method stub ClassName::method() does not exist.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
