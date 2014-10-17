<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Exception;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Exception;
use PHPUnit_Framework_TestCase;

class MockGenerationFailedExceptionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockBuilder = new MockBuilder(null, null, 'ClassName');
        $this->cause = new Exception();
    }

    public function testException()
    {
        $source = <<<'EOD'
// this line is NOT context
// this line is context
// this line is context
// this line is context
ERROR
// this line is context
// this line is context
// this line is context
// this line is NOT context
EOD;
        $error = array('message' => 'errorMessage', 'line' => 5);
        $exception = new MockGenerationFailedException($this->mockBuilder, $source, $error, $this->cause);
        if (defined('HHVM_VERSION')) {
            $expected = <<<'EOD'
Mock class ClassName generation failed.
Relevant lines:
    1: // this line is NOT context
    2: // this line is context
    3: // this line is context
    4: // this line is context
    5: ERROR
    6: // this line is context
    7: // this line is context
    8: // this line is context
    9: // this line is NOT context
EOD;
        } else {
            $expected = <<<'EOD'
Mock class ClassName generation failed: errorMessage in generated code on line 5.
Relevant lines:
    2: // this line is context
    3: // this line is context
    4: // this line is context
    5: ERROR
    6: // this line is context
    7: // this line is context
    8: // this line is context
EOD;
        }

        $this->assertSame($this->mockBuilder, $exception->mockBuilder());
        $this->assertSame($source, $exception->source());
        $this->assertSame($error, $exception->error());
        $this->assertSame($expected, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($this->cause, $exception->getPrevious());
    }
}
