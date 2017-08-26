<?php

declare(strict_types=1);

namespace Eloquent\Phony\Clock;

/**
 * The interface implemented by clocks.
 */
interface Clock
{
    /**
     * Get the current time.
     *
     * @return float The current time.
     */
    public function time(): float;
}
