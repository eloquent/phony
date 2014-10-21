<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event\Verification;

use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Exception;

/**
 * The interface implemented by event order verifiers.
 */
interface EventOrderVerifierInterface
{
    /**
     * Checks if the supplied events happened in chronological order.
     *
     * @param CallEventCollectionInterface $events,... The events.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkInOrder();

    /**
     * Throws an exception unless the supplied events happened in chronological
     * order.
     *
     * @param CallEventCollectionInterface $events,... The events.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public function inOrder();

    /**
     * Checks if the supplied event sequence happened in chronological order.
     *
     * @param mixed<CallEventCollectionInterface> $events The event sequence.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkInOrderSequence($events);

    /**
     * Throws an exception unless the supplied event sequence happened in
     * chronological order.
     *
     * @param mixed<CallEventCollectionInterface> $events The event sequence.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails.
     */
    public function inOrderSequence($events);
}
