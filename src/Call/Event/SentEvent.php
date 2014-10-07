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

use Eloquent\Phony\Event\AbstractEvent;

/**
 * Represents a value sent to a generator.
 *
 * @internal
 */
class SentEvent extends AbstractEvent implements SentEventInterface
{
    /**
     * Construct a 'sent value' event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed   $value          The sent value.
     */
    public function __construct($sequenceNumber, $time, $value = null)
    {
        parent::__construct($sequenceNumber, $time);

        $this->value = $value;
    }

    /**
     * Get the sent value.
     *
     * @return mixed The sent value.
     */
    public function value()
    {
        return $this->value;
    }

    private $value;
}
