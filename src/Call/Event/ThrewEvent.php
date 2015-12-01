<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Error;
use Exception;

/**
 * Represents the end of a call by throwing an exception.
 */
class ThrewEvent extends AbstractCallEvent implements ThrewEventInterface
{
    /**
     * Construct a 'threw' event.
     *
     * @param integer              $sequenceNumber The sequence number.
     * @param float                $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Exception|Error|null $exception      The thrown exception.
     */
    public function __construct($sequenceNumber, $time, $exception = null)
    {
        if (null === $exception) {
            $exception = new Exception();
        }

        parent::__construct($sequenceNumber, $time);

        $this->exception = $exception;
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|Error The thrown exception.
     */
    public function exception()
    {
        return $this->exception;
    }

    private $exception;
}
