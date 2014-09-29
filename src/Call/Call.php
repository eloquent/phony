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
use ReflectionFunctionAbstract;

/**
 * Represents a single call.
 *
 * @internal
 */
class Call implements CallInterface
{
    /**
     * Construct a new call.
     *
     * @param ReflectionFunctionAbstract $reflector      The function or method called.
     * @param array<integer,mixed>       $arguments      The arguments.
     * @param mixed                      $returnValue    The return value.
     * @param integer                    $sequenceNumber The sequence number.
     * @param float                      $startTime      The time at which the call was made, in seconds since the Unix epoch.
     * @param float                      $endTime        The time at which the call completed, in seconds since the Unix epoch.
     * @param Exception|null             $exception      The thrown exception, or null if no exception was thrown.
     * @param object|null                $thisValue      The $this value, or null if unbound.
     */
    public function __construct(
        ReflectionFunctionAbstract $reflector,
        array $arguments,
        $returnValue,
        $sequenceNumber,
        $startTime,
        $endTime,
        Exception $exception = null,
        $thisValue = null
    ) {
        $this->reflector = $reflector;
        $this->arguments = $arguments;
        $this->returnValue = $returnValue;
        $this->sequenceNumber = $sequenceNumber;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->exception = $exception;
        $this->thisValue = $thisValue;
    }

    /**
     * Get the function or method called.
     *
     * @return ReflectionFunctionAbstract The function or method called.
     */
    public function reflector()
    {
        return $this->reflector;
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
     * @return object|null The $this value, or null if unbound.
     */
    public function thisValue()
    {
        return $this->thisValue;
    }

    private $reflector;
    private $arguments;
    private $returnValue;
    private $sequenceNumber;
    private $startTime;
    private $endTime;
    private $thisValue;
    private $exception;
}
