<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;

/**
 * The interface implemented by call requests.
 */
interface CallRequestInterface
{
    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback();

    /**
     * Get the final arguments.
     *
     * @param object                   $self      The self value.
     * @param ArgumentsInterface|array $arguments The incoming arguments.
     *
     * @return ArgumentsInterface The final arguments.
     */
    public function finalArguments($self, $arguments = array());

    /**
     * Get the hard-coded arguments.
     *
     * @return ArgumentsInterface The hard-coded arguments.
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
     * arguments as an object.
     *
     * @return boolean True if arguments object should be appended.
     */
    public function suffixArgumentsObject();

    /**
     * Returns true if the incoming arguments should be appended to the final
     * arguments.
     *
     * @return boolean True if arguments should be appended.
     */
    public function suffixArguments();
}
