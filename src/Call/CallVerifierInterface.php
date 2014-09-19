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

/**
 * The interface implemented by call verifiers.
 */
interface CallVerifierInterface extends CallInterface
{
    /**
     * Get the call duration.
     *
     * @return float The call duration, in seconds.
     */
    public function duration();

    /**
     * Get the number of arguments.
     *
     * @return integer The number of arguments.
     */
    public function argumentCount();

    /**
     * Returns true if called with the supplied arguments (and possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if called with the supplied arguments.
     */
    public function calledWith();

    /**
     * Returns true if called with the supplied arguments and no others.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if called with the supplied arguments.
     */
    public function calledWithExactly();

    /**
     * Returns true if not called with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if not called with the supplied arguments.
     */
    public function notCalledWith();

    /**
     * Returns true if not called with the supplied arguments and no others.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if not called with the supplied arguments.
     */
    public function notCalledWithExactly();

    /**
     * Returns true if this call occurred before the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return boolean True if this call occurred before the supplied call.
     */
    public function calledBefore(CallInterface $call);

    /**
     * Returns true if this call occurred after the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return boolean True if this call occurred after the supplied call.
     */
    public function calledAfter(CallInterface $call);

    /**
     * Returns true if the $this value is the same as the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return boolean True if the $this value is the same as the supplied value.
     */
    public function calledOn($value);

    /**
     * Returns true if this call returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return boolean True if this call returned the supplied value.
     */
    public function returned($value);

    /**
     * Returns true if an exception of the supplied type was thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean True if a matching exception was thrown.
     */
    public function threw($type = null);
}
