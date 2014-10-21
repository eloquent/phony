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

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\Event\CallEventCollection;
use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Exception;

/**
 * An assertion recorder that simply throws the supplied exceptions.
 *
 * @internal
 */
class AssertionRecorder implements AssertionRecorderInterface
{
    /**
     * Get the static instance of this recorder.
     *
     * @return AssertionRecorderInterface The static recorder.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Record that a successful assertion occurred.
     *
     * @param array<integer,EventInterface>|null $events The events.
     *
     * @return CallEventCollectionInterface The result.
     */
    public function createSuccess(array $events = null)
    {
        return new CallEventCollection($events);
    }

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @return Exception The appropriate assertion failure exception.
     */
    public function createFailure($description)
    {
        return new AssertionException($description);
    }

    private static $instance;
}
