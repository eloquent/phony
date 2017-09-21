<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Event\Event;

class TestEvent implements Event
{
    public function __construct(int $sequenceNumber, float $time)
    {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
    }

    public function sequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function time(): float
    {
        return $this->time;
    }

    private $sequenceNumber;
    private $time;
}
