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
 * Represents the end of a call by throwing an exception.
 *
 * @internal
 */
class ThrewEvent extends AbstractCallEvent implements ThrewEventInterface
{
    /**
     * Construct a 'threw' event.
     *
     * @param Exception $exception      The thrown exception.
     * @param integer   $sequenceNumber The sequence number.
     * @param float     $time           The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function __construct(Exception $exception, $sequenceNumber, $time)
    {
        parent::__construct($sequenceNumber, $time);

        $this->exception = $exception;
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception The thrown exception.
     */
    public function exception()
    {
        return $this->exception;
    }

    private $exception;
}
