<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class MultipleInheritanceExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $classNames = array('ClassNameA', 'ClassNameB', 'ClassNameC');
        $cause = new Exception();
        $exception = new MultipleInheritanceException($classNames, $cause);

        $this->assertSame($classNames, $exception->classNames());
        $this->assertSame(
            "Unable to extend 'ClassNameA' and 'ClassNameB' and 'ClassNameC' simultaneously.",
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
