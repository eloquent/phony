<?php

declare(strict_types=1);

namespace Eloquent\Phony\Clock;

/**
 * Provides access to the system clock.
 */
class SystemClock implements Clock
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self('microtime');
        }

        return self::$instance;
    }

    /**
     * Construct a new system clock.
     *
     * @param callable $microtime The implementation of microtime() to use.
     */
    public function __construct(callable $microtime)
    {
        $this->microtime = $microtime;
    }

    /**
     * Get the current time.
     *
     * @return float The current time.
     */
    public function time(): float
    {
        $microtime = $this->microtime;

        return $microtime(true);
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var callable
     */
    private $microtime;
}
