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

use Eloquent\Phony\Assertion\AssertionRecorder;

class TestAssertionRecorder extends AssertionRecorder
{
    public function calls()
    {
        return $this->calls;
    }

    public function recordSuccess()
    {
        $this->calls[] = array('recordSuccess');
    }

    public function recordFailure($description)
    {
        $this->calls[] = array('recordFailure', $description);

        parent::recordFailure($description);
    }

    private $calls = array();
}
