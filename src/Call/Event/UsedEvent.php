<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

/**
 * Represents the start of iteration of a returned value.
 */
class UsedEvent implements IterableEvent
{
    use CallEventTrait;

    /**
     * Construct a new 'used' event.
     *
     * @param int   $sequenceNumber The sequence number.
     * @param float $time           The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function __construct(int $sequenceNumber, float $time)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
    }
}
