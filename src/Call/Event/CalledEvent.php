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

use ReflectionFunctionAbstract;

/**
 * Represents the start of a call.
 */
class CalledEvent extends AbstractCallEvent implements CalledEventInterface
{
    /**
     * Construct a new 'called' event.
     *
     * @param ReflectionFunctionAbstract $reflector      The function or method called.
     * @param object|null                $thisValue      The $this value, or null if unbound.
     * @param array<integer,mixed>       $arguments      The arguments.
     * @param integer                    $sequenceNumber The sequence number.
     * @param float                      $time           The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function __construct(
        ReflectionFunctionAbstract $reflector,
        $thisValue,
        array $arguments,
        $sequenceNumber,
        $time
    ) {
        parent::__construct($sequenceNumber, $time);

        $this->reflector = $reflector;
        $this->thisValue = $thisValue;
        $this->arguments = $arguments;
    }

    /**
     * Get the called function or method called.
     *
     * @return ReflectionFunctionAbstract The function or method called.
     */
    public function reflector()
    {
        return $this->reflector;
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

    /**
     * Get the received arguments.
     *
     * @return array<integer,mixed> The received arguments.
     */
    public function arguments()
    {
        return $this->arguments;
    }

    private $reflector;
    private $thisValue;
    private $arguments;
}
