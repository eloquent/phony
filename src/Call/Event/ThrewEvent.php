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
use Exception;

/**
 * Represents the end of a call by throwing an exception.
 *
 * @internal
 */
class ThrewEvent extends AbstractEvent implements ThrewEventInterface
{
    /**
     * Construct a 'threw' event.
     *
     * @param integer        $sequenceNumber The sequence number.
     * @param float          $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Exception|null $exception      The thrown exception.
     */
    public function __construct(
        $sequenceNumber,
        $time,
        Exception $exception = null
    ) {
        if (null === $exception) {
            $exception = new Exception();
        }

        parent::__construct($sequenceNumber, $time);

        $this->exception = $exception;
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception The thrown exception.
     */
    public function exception()
    {
        return $this->exception;
    }

    private $exception;
}
