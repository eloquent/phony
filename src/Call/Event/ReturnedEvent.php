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
 * Represents the end of a call by returning a value.
 *
 * @internal
 */
class ReturnedEvent extends AbstractCallEvent implements ReturnedEventInterface
{
    /**
     * Construct a 'returned' event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed   $value          The return value.
     */
    public function __construct($sequenceNumber, $time, $value = null)
    {
        parent::__construct($sequenceNumber, $time);

        $this->value = $value;
    }

    /**
     * Get the returned value.
     *
     * @return mixed The returned value.
     */
    public function value()
    {
        return $this->value;
    }

    private $value;
}
