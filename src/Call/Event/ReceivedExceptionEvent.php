<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use Throwable;

/**
 * Represents an exception received by a generator.
 */
class ReceivedExceptionEvent implements IterableEvent
{
    use CallEventTrait;

    /**
     * Construct a 'received exception' event.
     *
     * @param int       $sequenceNumber The sequence number.
     * @param float     $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Throwable $exception      The received exception.
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
     * Get the received exception.
     *
     * @return Throwable The received exception.
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
