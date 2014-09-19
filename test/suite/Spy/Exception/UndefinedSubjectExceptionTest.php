<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Exception;

use Eloquent\Phony\Spy\Spy;
use Exception;
use PHPUnit_Framework_TestCase;

class UndefinedSubjectExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $spy = new Spy;
        $cause = new Exception;
        $exception = new UndefinedSubjectException($spy, $cause);

        $this->assertSame($spy, $exception->spy());
        $this->assertSame("The requested spy subject does not exist.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
