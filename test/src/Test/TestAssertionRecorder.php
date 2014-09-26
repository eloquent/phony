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
use Eloquent\Phony\Assertion\Exception\AssertionExceptionInterface;

class TestAssertionRecorder extends AssertionRecorder
{
    public function calls()
    {
        return $this->calls;
    }

    /**
     * Record that a successful assertion occurred.
     */
    public function recordSuccess()
    {
        $this->calls[] = array('recordSuccess');
    }

    /**
     * Record that an assertion failure occurred.
     *
     * @param AssertionExceptionInterface $failure The failure.
     *
     * @throws Exception The appropriate assertion exception.
     */
    public function recordFailure(AssertionExceptionInterface $failure)
    {
        $this->calls[] = array('recordFailure', $failure);

        parent::recordFailure($failure);
    }

    private $calls = array();
}
