<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

/**
 * Represents a value received by a generator.
 */
class ReceivedEvent implements IterableEvent
{
    use CallEventTrait;

    /**
     * Construct a 'received' event.
     *
     * @param int   $sequenceNumber The sequence number.
     * @param float $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed $value          The received value.
     */
    public function __construct(int $sequenceNumber, float $time, $value)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
        $this->value = $value;
    }

    /**
     * Get the received value.
     *
     * @return mixed The received value.
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @var mixed
     */
    private $value;
}
