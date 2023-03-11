<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;

/**
 * Represents a call request.
 */
class CallRequest
{
    /**
     * Construct a call request.
     *
     * @param callable  $callback              The callback.
     * @param Arguments $arguments             The arguments.
     * @param bool      $prefixSelf            True if the self value should be prefixed.
     * @param bool      $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool      $suffixArguments       True if the arguments should be appended individually.
     */
    public function __construct(
        callable $callback,
        Arguments $arguments,
        bool $prefixSelf,
        bool $suffixArgumentsObject,
        bool $suffixArguments
    ) {
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->prefixSelf = $prefixSelf;
        $this->suffixArgumentsObject = $suffixArgumentsObject;
        $this->suffixArguments = $suffixArguments;

        foreach ($this->arguments->positional() as $index => $argument) {
            if ($argument instanceof InstanceHandle) {
                $this->arguments->set($index, $argument->get());
            }
        }
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
     * Get the final arguments.
     *
     * @param mixed     $self      The self value.
     * @param Arguments $arguments The incoming arguments.
     *
     * @return Arguments The final arguments.
     */
    public function finalArguments(
        mixed $self,
        Arguments $arguments
    ): Arguments {
        $finalArguments = $this->arguments->positional();

        if ($this->prefixSelf) {
            array_unshift($finalArguments, $self);
        }
        if ($this->suffixArgumentsObject) {
            $finalArguments[] = $arguments;
        }
        if ($this->suffixArguments) {
            $finalArguments =
                array_merge($finalArguments, $arguments->positional());
        }

        return new Arguments($finalArguments);
    }

    /**
     * Get the hard-coded arguments.
     *
     * @return Arguments The hard-coded arguments.
     */
    public function arguments(): Arguments
    {
        return $this->arguments;
    }

    /**
     * Returns true if the self value should be prefixed to the final arguments.
     *
     * @return bool True if the self value should be prefixed.
     */
    public function prefixSelf(): bool
    {
        return $this->prefixSelf;
    }

    /**
     * Returns true if the incoming arguments should be appended to the final
     * arguments as an object.
     *
     * @return bool True if arguments object should be appended.
     */
    public function suffixArgumentsObject(): bool
    {
        return $this->suffixArgumentsObject;
    }

    /**
     * Returns true if the incoming arguments should be appended to the final
     * arguments.
     *
     * @return bool True if arguments should be appended.
     */
    public function suffixArguments(): bool
    {
        return $this->suffixArguments;
    }

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var Arguments
     */
    private $arguments;

    /**
     * @var bool
     */
    private $prefixSelf;

    /**
     * @var bool
     */
    private $suffixArgumentsObject;

    /**
     * @var bool
     */
    private $suffixArguments;
}
