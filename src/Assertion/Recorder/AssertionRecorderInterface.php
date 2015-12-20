<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Recorder;

use Eloquent\Phony\Event\EventCollectionInterface;
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
     * @param array<EventInterface> $events The events.
     *
     * @return EventCollectionInterface The result.
     */
    public function createSuccess(array $events = array());

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @throws Exception If this recorder throws exceptions.
     */
    public function createFailure($description);
}
