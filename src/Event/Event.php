<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event;

/**
 * The interface implemented by events.
 */
interface Event
{
    /**
     * Get the sequence number.
     *
     * The sequence number is a unique number assigned to every event that Phony
     * records. The numbers are assigned sequentially, meaning that sequence
     * numbers can be used to determine event order.
     *
     * @return int The sequence number.
     */
    public function sequenceNumber(): int;

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time(): float;
}
