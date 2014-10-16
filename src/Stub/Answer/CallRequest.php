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
class CallRequest extends AbstractCallRequest
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
        parent::__construct(
            $arguments,
            $prefixSelf,
            $suffixArgumentsArray,
            $suffixArguments
        );

        $this->callback = $callback;
    }

    /**
     * Get the callback.
     *
     * @param array<integer,mixed>|null $arguments The incoming arguments.
     *
     * @return callable|null The callback, or null if no callback is available.
     */
    public function callback(array $arguments = null)
    {
        return $this->callback;
    }

    private $callback;
}
