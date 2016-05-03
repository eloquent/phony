<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\EventSequence;
use Exception;

/**
 * An assertion recorder that throws exceptions on failure.
 */
class ExceptionAssertionRecorder implements AssertionRecorder
{
    /**
     * Get the static instance of this recorder.
     *
     * @return AssertionRecorder The static recorder.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Record that a successful assertion occurred.
     *
     * @param array<Event> $events The events.
     *
     * @return EventCollection The result.
     */
    public function createSuccess(array $events = array())
    {
        return new EventSequence($events);
    }

    /**
     * Record that a successful assertion occurred.
     *
     * @param EventCollection $events The events.
     *
     * @return EventCollection The result.
     */
    public function createSuccessFromEventCollection(EventCollection $events)
    {
        return $events;
    }

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @throws Exception If this recorder throws exceptions.
     */
    public function createFailure($description)
    {
        throw new AssertionException($description);
    }

    private static $instance;
}
