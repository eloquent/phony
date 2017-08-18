<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification\Exception;

use Eloquent\Phony\Verification\Cardinality;
use PHPUnit\Framework\TestCase;

class InvalidSingularCardinalityExceptionTest extends TestCase
{
    public function testException()
    {
        $cardinality = new Cardinality();
        $exception = new InvalidSingularCardinalityException($cardinality);

        $this->assertSame($cardinality, $exception->cardinality());
        $this->assertSame(
            'The specified cardinality is invalid for events that can only happen once or not at all.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
