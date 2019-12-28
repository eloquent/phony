<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Call;

/**
 * Used for implementing call events.
 */
trait CallEventTrait
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

    /**
     * Set the call.
     *
     * @param Call $call The call.
     *
     * @return $this This event.
     */
    public function setCall(Call $call): CallEvent
    {
        $this->call = $call;

        return $this;
    }

    /**
     * Get the call.
     *
     * @return ?Call The call, or null if no call has been set.
     */
    public function call(): ?Call
    {
        return $this->call;
    }

    /**
     * @var int
     */
    private $sequenceNumber;

    /**
     * @var float
     */
    private $time;

    /**
     * @var ?Call
     */
    private $call;
}
