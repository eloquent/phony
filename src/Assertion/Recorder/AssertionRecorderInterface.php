<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Recorder;

use Eloquent\Phony\Assertion\Result\AssertionResultInterface;
use Eloquent\Phony\Event\EventInterface;
use Exception;

/**
 * The interface implemented by assertion recorders.
 */
interface AssertionRecorderInterface
{
    /**
     * Record that a successful assertion occurred.
     *
     * @param array<integer,EventInterface>|null $events The events.
     *
     * @return AssertionResultInterface An assertion result.
     */
    public function createSuccess(array $events = null);

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @return Exception The appropriate assertion failure exception.
     */
    public function createFailure($description);
}
