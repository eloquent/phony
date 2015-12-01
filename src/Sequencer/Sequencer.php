<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Sequencer;

/**
 * Provides a sequential series of numbers.
 */
class Sequencer implements SequencerInterface
{
    /**
     * Get a sequencer for a named sequence.
     *
     * @param string $name The sequence name.
     *
     * @return SequencerInterface The sequencer.
     */
    public static function sequence($name)
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self();
        }

        return self::$instances[$name];
    }

    /**
     * Set the sequence number.
     *
     * @param integer $current The sequence number.
     */
    public function set($current)
    {
        $this->current = $current;
    }

    /**
     * Reset the sequence number to its initial value.
     */
    public function reset()
    {
        $this->current = -1;
    }

    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function get()
    {
        return $this->current;
    }

    /**
     * Increment and return the sequence number.
     *
     * @return integer The sequence number.
     */
    public function next()
    {
        return ++$this->current;
    }

    private static $instances = array();
    private $current = -1;
}
