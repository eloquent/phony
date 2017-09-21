<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Clock\Clock;

class TestClock implements Clock
{
    public function time(): float
    {
        return $this->time += 1.0;
    }

    public function setTime($time)
    {
        $this->time = $time - 1.0;
    }

    public function reset()
    {
        $this->time = -1.0;
    }

    private $time = -1.0;
}
