<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Error;
use Exception;

/**
 * Represents an exception received by a generator.
 */
class ReceivedExceptionEvent extends AbstractCallEvent implements
    ReceivedExceptionEventInterface
{
    /**
     * Construct a 'received exception' event.
     *
     * @param integer              $sequenceNumber The sequence number.
     * @param float                $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Exception|Error|null $exception      The received exception.
     */
    public function __construct($sequenceNumber, $time, $exception = null)
    {
        if (!$exception) {
            $exception = new Exception();
        }

        parent::__construct($sequenceNumber, $time);

        $this->exception = $exception;
    }

    /**
     * Get the received exception.
     *
     * @return Exception|Error The received exception.
     */
    public function exception()
    {
        return $this->exception;
    }

    private $exception;
}
