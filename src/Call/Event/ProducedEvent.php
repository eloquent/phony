<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

/**
 * Represents a produced key-value pair.
 */
class ProducedEvent extends AbstractCallEvent implements ProducedEventInterface
{
    /**
     * Construct a 'produced' event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed   $keyOrValue     The produced key or value.
     * @param mixed   $value          The produced value.
     */
    public function __construct(
        $sequenceNumber,
        $time,
        $keyOrValue = null,
        $value = null
    ) {
        parent::__construct($sequenceNumber, $time);

        if (func_num_args() > 3) {
            $this->key = $keyOrValue;
            $this->value = $value;
        } else {
            $this->value = $keyOrValue;
        }
    }

    /**
     * Get the produced key.
     *
     * @return mixed The produced key.
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get the produced value.
     *
     * @return mixed The produced value.
     */
    public function value()
    {
        return $this->value;
    }

    private $key;
    private $value;
}
