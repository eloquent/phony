<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;

/**
 * Represents a call request.
 */
class CallRequest implements CallRequestInterface
{
    /**
     * Construct a call request.
     *
     * @param callable                      $callback              The callback.
     * @param ArgumentsInterface|array|null $arguments             The arguments.
     * @param boolean|null                  $prefixSelf            True if the self value should be prefixed.
     * @param boolean|null                  $suffixArgumentsObject True if arguments object should be appended.
     * @param boolean|null                  $suffixArguments       True if arguments should be appended.
     */
    public function __construct(
        $callback,
        $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsObject = null,
        $suffixArguments = null
    ) {
        if (null === $prefixSelf) {
            $prefixSelf = false;
        }
        if (null === $suffixArgumentsObject) {
            $suffixArgumentsObject = false;
        }
        if (null === $suffixArguments) {
            $suffixArguments = true;
        }

        $this->callback = $callback;
        $this->arguments = Arguments::adapt($arguments);
        $this->prefixSelf = $prefixSelf;
        $this->suffixArgumentsObject = $suffixArgumentsObject;
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
     * @param object                        $self      The self value.
     * @param ArgumentsInterface|array|null $arguments The incoming arguments.
     *
     * @return ArgumentsInterface The final arguments.
     */
    public function finalArguments($self, $arguments = null)
    {
        $arguments = Arguments::adapt($arguments);
        $finalArguments = $this->arguments->all();

        if ($this->prefixSelf) {
            array_unshift($finalArguments, $self);
        }
        if ($this->suffixArgumentsObject) {
            $finalArguments[] = $arguments;
        }

        if ($this->suffixArguments) {
            $finalArguments = array_merge($finalArguments, $arguments->all());
        }

        return new Arguments($finalArguments);
    }

    /**
     * Get the hard-coded arguments.
     *
     * @return ArgumentsInterface The hard-coded arguments.
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
     * arguments as an object.
     *
     * @return boolean True if arguments object should be appended.
     */
    public function suffixArgumentsObject()
    {
        return $this->suffixArgumentsObject;
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
    private $suffixArgumentsObject;
    private $suffixArguments;
}
