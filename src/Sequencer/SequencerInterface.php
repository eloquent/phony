<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Sequencer;

/**
 * The interface implemented by sequencers.
 */
interface SequencerInterface
{
    /**
     * Set the sequence number.
     *
     * @param integer $current The sequence number.
     */
    public function set($current);

    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function get();

    /**
     * Increment and return the sequence number.
     *
     * @return integer The sequence number.
     */
    public function next();
}
