<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

/**
 * Represents a produced key-value pair.
 */
class ProducedEvent implements IterableEvent
{
    use CallEventTrait;

    /**
     * Construct a 'produced' event.
     *
     * @param int   $sequenceNumber The sequence number.
     * @param float $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed $key            The produced key.
     * @param mixed $value          The produced value.
     */
    public function __construct(int $sequenceNumber, float $time, $key, $value)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Get the produced key.
     *
     * @return mixed The produced key.
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get the produced value.
     *
     * @return mixed The produced value.
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;
}
