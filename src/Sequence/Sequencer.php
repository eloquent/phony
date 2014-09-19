<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Sequence;

/**
 * Provides a sequential series of numbers.
 */
class Sequencer implements SequencerInterface
{
    /**
     * Get the static instance of this sequencer.
     *
     * @return SequencerInterface The static sequencer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
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

    private static $instance;
    private $current = -1;
}
