<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

/**
 * Represents the end of iteration of a returned value.
 */
class ConsumedEvent implements EndEvent
{
    use CallEventTrait;

    /**
     * Construct a new 'consumed' event.
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
