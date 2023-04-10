<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Arguments;
use ReflectionParameter;

/**
 * Represents the start of a call.
 */
class CalledEvent implements CallEvent
{
    use CallEventTrait;

    /**
     * Construct a new 'called' event.
     *
     * @param int                            $sequenceNumber The sequence number.
     * @param float                          $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param callable                       $callback       The callback.
     * @param array<int,ReflectionParameter> $parameters     The parameters.
     * @param Arguments                      $arguments      The arguments.
     */
    public function __construct(
        int $sequenceNumber,
        float $time,
        $callback,
        array $parameters,
        Arguments $arguments
    ) {
        $this->sequenceNumber = $sequenceNumber;
        $this->time = $time;
        $this->callback = $callback;
        $this->parameters = $parameters;
        $this->arguments = $arguments;
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback(): callable
    {
        return $this->callback;
    }

    /**
     * Get the parameters.
     *
     * @return array<int,ReflectionParameter> The parameters.
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the parameter names.
     *
     * @return array<int,string> The parameter names.
     */
    public function parameterNames(): array
    {
        $names = [];

        foreach ($this->parameters as $parameter) {
            if ($parameter->isVariadic()) {
                break;
            }

            $names[] = $parameter->getName();
        }

        return $names;
    }

    /**
     * Get the received arguments.
     *
     * @return Arguments The received arguments.
     */
    public function arguments(): Arguments
    {
        return $this->arguments;
    }

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var array<int,ReflectionParameter>
     */
    private $parameters;

    /**
     * @var Arguments
     */
    private $arguments;
}
