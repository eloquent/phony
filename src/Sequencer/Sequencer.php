<?php

declare(strict_types=1);

namespace Eloquent\Phony\Sequencer;

/**
 * Provides a sequential series of numbers.
 */
class Sequencer
{
    /**
     * Get a sequencer for a named sequence.
     *
     * @param string $name The sequence name.
     *
     * @return Sequencer The sequencer.
     */
    public static function sequence(string $name): self
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self();
        }

        return self::$instances[$name];
    }

    /**
     * Set the sequence number.
     *
     * @param int $current The sequence number.
     */
    public function set(int $current): void
    {
        $this->current = $current;
    }

    /**
     * Reset the sequence number to its initial value.
     */
    public function reset(): void
    {
        $this->current = -1;
    }

    /**
     * Get the sequence number.
     *
     * @return int The sequence number.
     */
    public function get(): int
    {
        return $this->current;
    }

    /**
     * Increment and return the sequence number.
     *
     * @return int The sequence number.
     */
    public function next(): int
    {
        return ++$this->current;
    }

    /**
     * @var array<string,self>
     */
    private static $instances = [];

    /**
     * @var int
     */
    private $current = -1;
}
