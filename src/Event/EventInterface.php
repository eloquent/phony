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
 * The interface implemented by events.
 */
interface EventInterface
{
    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber();

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time();
}
