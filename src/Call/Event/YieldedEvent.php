<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

/**
 * Represents a yielded key-value pair.
 *
 * @internal
 */
class YieldedEvent extends AbstractCallEvent implements YieldedEventInterface
{
    /**
     * Construct a 'yielded' event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed   $keyOrValue     The yielded key or value.
     * @param mixed   $value          The yielded value.
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
     * Get the yielded key.
     *
     * @return mixed The yielded key.
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get the yielded value.
     *
     * @return mixed The yielded value.
     */
    public function value()
    {
        return $this->value;
    }

    private $key;
    private $value;
}
