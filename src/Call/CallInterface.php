<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Exception;

/**
 * The interface implemented by calls.
 */
interface CallInterface
{
    /**
     * Get the received arguments.
     *
     * @return array<integer,mixed> The received arguments.
     */
    public function arguments();

    /**
     * Get the returned value.
     *
     * @return mixed The returned value.
     */
    public function returnValue();

    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber();

    /**
     * Get the time at which the call was made.
     *
     * @return float The time at which the call was made, in seconds since the Unix epoch.
     */
    public function startTime();

    /**
     * Get the time at which the call completed.
     *
     * @return float The time at which the call completed, in seconds since the Unix epoch.
     */
    public function endTime();

    /**
     * Get the thrown exception.
     *
     * @return Exception|null The thrown exception, or null if no exception was thrown.
     */
    public function exception();

    /**
     * Get the $this value.
     *
     * @return object|null The $this value, or null if unbound.
     */
    public function thisValue();
}
