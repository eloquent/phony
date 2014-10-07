<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;

class TestAssertionRecorder extends AssertionRecorder
{
    public function recordSuccess()
    {
        $this->successCount++;
    }

    public function successCount()
    {
        return $this->successCount;
    }

    private $successCount = 0;
}
