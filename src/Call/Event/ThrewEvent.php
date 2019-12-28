<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use Throwable;

/**
 * Represents the end of a call by throwing an exception.
 */
class ThrewEvent implements ResponseEvent
{
    use CallEventTrait;

    /**
     * Construct a 'threw' event.
     *
     * @param int       $sequenceNumber The sequence number.
     * @param float     $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Throwable $exception      The thrown exception.
     */
    public function __construct(
        int $sequenceNumber,
        float $time,
        Throwable $exception
    ) {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
        $this->exception = $exception;
    }

    /**
     * Get the thrown exception.
     *
     * @return Throwable The thrown exception.
     */
    public function exception(): Throwable
    {
        return $this->exception;
    }

    /**
     * @var Throwable
     */
    private $exception;
}
