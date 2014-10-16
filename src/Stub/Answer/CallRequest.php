<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer;

/**
 * Represents a call request.
 *
 * @internal
 */
class CallRequest implements CallRequestInterface
{
    /**
     * Construct a call request.
     *
     * @param callable                  $callback             The callback.
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     */
    public function __construct(
        $callback,
        array $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        if (null === $arguments) {
            $arguments = array();
        }
        if (null === $prefixSelf) {
            $prefixSelf = false;
        }
        if (null === $suffixArgumentsArray) {
            $suffixArgumentsArray = false;
        }
        if (null === $suffixArguments) {
            $suffixArguments = true;
        }

        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->prefixSelf = $prefixSelf;
        $this->suffixArgumentsArray = $suffixArgumentsArray;
        $this->suffixArguments = $suffixArguments;
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
     * Get the final arguments.
     *
     * @param object                    $self      The self value.
     * @param array<integer,mixed>|null $arguments The incoming arguments.
     *
     * @return array<integer,mixed> The final arguments.
     */
    public function finalArguments($self, array $arguments = null)
    {
        $finalArguments = $this->arguments;

        if ($this->prefixSelf) {
            array_unshift($finalArguments, $self);
        }
        if ($this->suffixArgumentsArray) {
            $finalArguments[] = $arguments;
        }

        if ($this->suffixArguments) {
            $finalArguments = array_merge($finalArguments, $arguments);
        }

        return $finalArguments;
    }

    /**
     * Get the hard-coded arguments.
     *
     * @return array<integer,mixed> The hard-coded arguments.
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * Returns true if the self value should be prefixed to the final arguments.
     *
     * @return boolean True if the self value should be prefixed.
     */
    public function prefixSelf()
    {
        return $this->prefixSelf;
    }

    /**
     * Returns true if the incoming arguments should be appended to the final
     * arguments as an array.
     *
     * @return boolean True if arguments should be appended as an array.
     */
    public function suffixArgumentsArray()
    {
        return $this->suffixArgumentsArray;
    }

    /**
     * Returns true if the incoming arguments should be appended to the final
     * arguments.
     *
     * @return boolean True if arguments should be appended.
     */
    public function suffixArguments()
    {
        return $this->suffixArguments;
    }

    private $callback;
    private $arguments;
    private $prefixSelf;
    private $suffixArgumentsArray;
    private $suffixArguments;
}
