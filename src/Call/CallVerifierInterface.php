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

use Eloquent\Phony\Cardinality\Exception\InvalidCardinalityExceptionInterface;
use Eloquent\Phony\Cardinality\Verification\CardinalityVerifierInterface;
use Exception;

/**
 * The interface implemented by call verifiers.
 */
interface CallVerifierInterface extends CallInterface,
    CardinalityVerifierInterface
{
    /**
     * Get the call duration.
     *
     * @return float|null The call duration in seconds, or null if the call has not yet completed.
     */
    public function duration();

    /**
     * Get the call response duration.
     *
     * @return float|null The call response duration in seconds, or null if the call has not yet responded.
     */
    public function responseDuration();

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
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledWith();

    /**
     * Throws an exception unless called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledWith();

    /**
     * Returns true if called with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledWithExactly();

    /**
     * Throws an exception unless called with the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledWithExactly();

    /**
     * Returns true if this call occurred before the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledBefore(CallInterface $call);

    /**
     * Throws an exception unless this call occurred before the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledBefore(CallInterface $call);

    /**
     * Returns true if this call occurred after the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledAfter(CallInterface $call);

    /**
     * Throws an exception unless this call occurred after the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledAfter(CallInterface $call);

    /**
     * Returns true if the $this value is equal to the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledOn($value);

    /**
     * Throws an exception unless the $this value is equal to the supplied
     * value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledOn($value);

    /**
     * Returns true if this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned.
     *
     * @param mixed $value The value.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkReturned($value = null);

    /**
     * Throws an exception unless this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned.
     *
     * @param mixed $value The value.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function returned($value = null);

    /**
     * Returns true if an exception of the supplied type was thrown.
     *
     * When called with no arguments, this method simply checks that the call
     * threw.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkThrew($type = null);

    /**
     * Throws an exception unless this call threw an exception of the supplied
     * type.
     *
     * When called with no arguments, this method simply checks that the call
     * threw.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function threw($type = null);

    /**
     * Returns true if this call yielded the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
     * yielded.
     *
     * With a single argument, it checks that a value matching the argument was
     * yielded.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were yielded together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return mixed The result.
     */
    public function checkYielded($keyOrValue = null, $value = null);

    /**
     * Throws an exception unless this call yielded the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
     * yielded.
     *
     * With a single argument, it checks that a value matching the argument was
     * yielded.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were yielded together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return mixed     The result.
     * @throws Exception If the assertion fails.
     */
    public function yielded($keyOrValue = null, $value = null);
}
