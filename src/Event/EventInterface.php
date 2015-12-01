<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

/**
 * The interface implemented by events.
 *
 * @api
 */
interface EventInterface extends EventCollectionInterface
{
    /**
     * Get the sequence number.
     *
     * The sequence number is a unique number assigned to every event that Phony
     * records. The numbers are assigned sequentially, meaning that sequence
     * numbers can be used to determine event order.
     *
     * @api
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber();

    /**
     * Get the time at which the event occurred.
     *
     * @api
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time();
}
