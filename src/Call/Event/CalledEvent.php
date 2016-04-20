<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;

/**
 * Represents the start of a call.
 */
class CalledEvent extends AbstractCallEvent implements CalledEventInterface
{
    /**
     * Construct a new 'called' event.
     *
     * @param integer            $sequenceNumber The sequence number.
     * @param float              $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param callable           $callback       The callback.
     * @param ArgumentsInterface $arguments      The arguments.
     */
    public function __construct(
        $sequenceNumber,
        $time,
        $callback,
        ArgumentsInterface $arguments
    ) {
        parent::__construct($sequenceNumber, $time);

        $this->callback = $callback;
        $this->arguments = $arguments;
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * Get the received arguments.
     *
     * @return ArgumentsInterface The received arguments.
     */
    public function arguments()
    {
        return $this->arguments;
    }

    private $callback;
    private $arguments;
}
