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
 * Represents an exception sent to a generator.
 *
 * @internal
 */
class SentExceptionEvent extends AbstractCallEvent implements
    SentExceptionEventInterface
{
    /**
     * Construct a 'sent exception' event.
     *
     * @param integer   $sequenceNumber The sequence number.
     * @param float     $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Exception $exception      The sent exception.
     */
    public function __construct(
        $sequenceNumber,
        $time,
        Exception $exception
    ) {
        parent::__construct($sequenceNumber, $time);

        $this->exception = $exception;
    }

    /**
     * Get the sent exception.
     *
     * @return Exception The sent exception.
     */
    public function exception()
    {
        return $this->exception;
    }

    private $exception;
}
