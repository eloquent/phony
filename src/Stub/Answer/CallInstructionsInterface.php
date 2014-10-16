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
 * The interface implemented by call instructions.
 */
interface CallInstructionsInterface
{
    /**
     * Get the callback.
     *
     * @param array<integer,mixed>|null $arguments The incoming arguments.
     *
     * @return callable|null The callback, or null if no callback is available.
     */
    public function callback(array $arguments = null);

    /**
     * Get the final arguments.
     *
     * @param object                    $self      The self value.
     * @param array<integer,mixed>|null $arguments The incoming arguments.
     *
     * @return array<integer,mixed> The final arguments.
     */
    public function finalArguments($self, array $arguments = null);

    /**
     * Get the hard-coded arguments.
     *
     * @return array<integer,mixed> The hard-coded arguments.
     */
    public function arguments();

    /**
     * Returns true if the self value should be prefixed to the final arguments.
     *
     * @return boolean True if the self value should be prefixed.
     */
    public function prefixSelf();

    /**
     * Returns true if the incoming arguments should be appended to the final
     * arguments as an array.
     *
     * @return boolean True if arguments should be appended as an array.
     */
    public function suffixArgumentsArray();

    /**
     * Returns true if the incoming arguments should be appended to the final
     * arguments.
     *
     * @return boolean True if arguments should be appended.
     */
    public function suffixArguments();
}
