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

class AnonymousClassExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new AnonymousClassException();

        $this->assertSame('Anonymous classes cannot be mocked.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
