<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Arguments;

/**
 * Represents the start of a call.
 */
class CalledEvent implements CallEvent
{
    use CallEventTrait;

    /**
     * Construct a new 'called' event.
     *
     * @param int       $sequenceNumber The sequence number.
     * @param float     $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param callable  $callback       The callback.
     * @param Arguments $arguments      The arguments.
     */
    public function __construct(
        int $sequenceNumber,
        float $time,
        $callback,
        Arguments $arguments
    ) {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
        $this->callback = $callback;
        $this->arguments = $arguments;
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback(): callable
    {
        return $this->callback;
    }

    /**
     * Get the received arguments.
     *
     * @return Arguments The received arguments.
     */
    public function arguments(): Arguments
    {
        return $this->arguments;
    }

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var Arguments
     */
    private $arguments;
}
