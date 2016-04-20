<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Feature\Exception;

use PHPUnit_Framework_TestCase;

class UndefinedFeatureExceptionTest extends PHPUnit_Framework_TestCase
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
