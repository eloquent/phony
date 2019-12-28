<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

/**
 * Represents the end of a call by returning a value.
 */
class ReturnedEvent implements ResponseEvent
{
    use CallEventTrait;

    /**
     * Construct a 'returned' event.
     *
     * @param int   $sequenceNumber The sequence number.
     * @param float $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed $value          The return value.
     */
    public function __construct(int $sequenceNumber, float $time, $value)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
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

    /**
     * @var mixed
     */
    private $value;
}
