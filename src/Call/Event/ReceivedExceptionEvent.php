<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Exception;

/**
 * Represents an exception received by a generator.
 *
 * @internal
 */
class ReceivedExceptionEvent extends AbstractCallEvent implements
    ReceivedExceptionEventInterface
{
    /**
     * Construct a 'received exception' event.
     *
     * @param integer        $sequenceNumber The sequence number.
     * @param float          $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Exception|null $exception      The received exception.
     */
    public function __construct(
        $sequenceNumber,
        $time,
        Exception $exception = null
    ) {
        if (null === $exception) {
            $exception = new Exception();
        }

        parent::__construct($sequenceNumber, $time);

        $this->exception = $exception;
    }

    /**
     * Get the received exception.
     *
     * @return Exception The received exception.
     */
    public function exception()
    {
        return $this->exception;
    }

    private $exception;
}
