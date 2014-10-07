<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

/**
 * An abstract base class for implementing events.
 *
 * @internal
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * Construct a new event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function __construct($sequenceNumber, $time)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
    }

    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time()
    {
        return $this->time;
    }

    private $sequenceNumber;
    private $time;
}
