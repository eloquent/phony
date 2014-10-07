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

use Eloquent\Phony\Event\AbstractEvent;

/**
 * Represents a yielded key-value pair.
 *
 * @internal
 */
class YieldedEvent extends AbstractEvent implements YieldedEventInterface
{
    /**
     * Construct a 'yielded' event.
     *
     * @param integer $sequenceNumber The sequence number.
     * @param float   $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param mixed   $value          The yielded value.
     * @param mixed   $key            The yielded key.
     */
    public function __construct(
        $sequenceNumber,
        $time,
        $value = null,
        $key = null
    ) {
        parent::__construct($sequenceNumber, $time);

        $this->value = $value;
        $this->key = $key;
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

    /**
     * Get the yielded key.
     *
     * @return mixed The yielded key.
     */
    public function key()
    {
        return $this->key;
    }

    private $value;
    private $key;
}
