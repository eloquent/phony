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
 * Represents a single call.
 */
class Call implements CallInterface
{
    /**
     * Construct a new call.
     *
     * @param array<integer,mixed> $arguments      The arguments.
     * @param mixed                $returnValue    The return value.
     * @param object               $thisValue      The $this value.
     * @param integer              $sequenceNumber The sequence number.
     * @param float                $startTime      The time at which the call was made, in seconds since the Unix epoch.
     * @param float                $endTime        The time at which the call completed, in seconds since the Unix epoch.
     * @param Exception|null       $exception      The thrown exception, or null if no exception was thrown.
     */
    public function __construct(
        array $arguments,
        $returnValue,
        $thisValue,
        $sequenceNumber,
        $startTime,
        $endTime,
        Exception $exception = null
    ) {
        $this->arguments = $arguments;
        $this->returnValue = $returnValue;
        $this->thisValue = $thisValue;
        $this->sequenceNumber = $sequenceNumber;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->exception = $exception;
    }

    /**
     * Get the received arguments.
     *
     * @return array<integer,mixed> The received arguments.
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * Get the return value.
     *
     * @return mixed The return value.
     */
    public function returnValue()
    {
        return $this->returnValue;
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|null The thrown exception, or null if no exception was thrown.
     */
    public function exception()
    {
        return $this->exception;
    }

    /**
     * Get the $this value.
     *
     * @return object The $this value.
     */
    public function thisValue()
    {
        return $this->thisValue;
    }

    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * Get the time at which the call was made.
     *
     * @return float The time at which the call was made, in seconds since the Unix epoch.
     */
    public function startTime()
    {
        return $this->startTime;
    }

    /**
     * Get the time at which the call completed.
     *
     * @return float The time at which the call completed, in seconds since the Unix epoch.
     */
    public function endTime()
    {
        return $this->endTime;
    }

    private $arguments;
    private $returnValue;
    private $thisValue;
    private $sequenceNumber;
    private $startTime;
    private $endTime;
    private $exception;
}
