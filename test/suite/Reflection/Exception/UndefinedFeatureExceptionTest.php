<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Reflection\Exception;

use PHPUnit\Framework\TestCase;

class UndefinedFeatureExceptionTest extends TestCase
{
    public function testException()
    {
        $feature = 'feature';
        $exception = new UndefinedFeatureException($feature);

        $this->assertSame($feature, $exception->feature());
        $this->assertSame("Undefined feature 'feature'.", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
