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

/**
 * Represents a value sent to a generator.
 *
 * @internal
 */
class SentValueEvent extends AbstractCallEvent implements
    SentValueEventInterface
{
    /**
     * Construct a 'sent value' event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed   $sentValue      The sent value.
     */
    public function __construct($sequenceNumber, $time, $sentValue = null)
    {
        parent::__construct($sequenceNumber, $time);

        $this->sentValue = $sentValue;
    }

    /**
     * Get the sent value.
     *
     * @return mixed The sent value.
     */
    public function sentValue()
    {
        return $this->sentValue;
    }

    private $sentValue;
}
