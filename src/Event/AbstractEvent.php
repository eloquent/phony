<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event;

/**
 * An abstract base class for implementing events.
 */
abstract class AbstractEvent implements Event
{
    /**
     * Construct a new event.
     *
     * @param int   $sequenceNumber The sequence number.
     * @param float $time           The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function __construct(int $sequenceNumber, float $time)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
    }

    /**
     * Get the sequence number.
     *
     * The sequence number is a unique number assigned to every event that Phony
     * records. The numbers are assigned sequentially, meaning that sequence
     * numbers can be used to determine event order.
     *
     * @return int The sequence number.
     */
    public function sequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time(): float
    {
        return $this->time;
    }

    private $sequenceNumber;
    private $time;
}
