<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\CallInterface;
use Exception;
use ReflectionFunctionAbstract;

/**
 * The interface implemented by call factories.
 */
interface CallFactoryInterface
{
    /**
     * Create a new call.
     *
     * @param ReflectionFunctionAbstract $subject        The function or method called.
     * @param array<integer,mixed>       $arguments      The arguments.
     * @param mixed                      $returnValue    The return value.
     * @param integer                    $sequenceNumber The sequence number.
     * @param float                      $startTime      The time at which the call was made, in seconds since the Unix epoch.
     * @param float                      $endTime        The time at which the call completed, in seconds since the Unix epoch.
     * @param Exception|null             $exception      The thrown exception, or null if no exception was thrown.
     * @param object|null                $thisValue      The $this value, or null if unbound.
     *
     * @return CallInterface The newly created call.
     */
    public function create(
        ReflectionFunctionAbstract $subject,
        array $arguments,
        $returnValue,
        $sequenceNumber,
        $startTime,
        $endTime,
        Exception $exception = null,
        $thisValue = null
    );
}
